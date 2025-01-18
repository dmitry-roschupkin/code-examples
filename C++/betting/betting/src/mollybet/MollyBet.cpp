#include "mollybet/MollyBet.h"

#include "client/HttpsClient.h"
#include "client/WssClient.h"

#include <boost/beast/http/status.hpp>
#include <boost/throw_exception.hpp>
#include <boost/json/src.hpp>

#include <fstream>
#include <iostream>
#include <stdexcept>

namespace betting::mollybet
{

MollyBet::MollyBet(ssl::context& ctx):
    pContext(&ctx)
{}


net::awaitable<std::string> MollyBet::takeToken()
{
    betting::client::HttpsClient client(host, port, *pContext);
    co_await client.connect();

    const auto contentType = "application/json";
    const std::string body = R"({"username":")" + username + R"(", "password":")" + password + R"("})";
    constexpr int version = 11;

    co_await client.sendSimpleRequest(method, sessionTarget, body, contentType, version);

    auto response(
        co_await client.readSimpleResponse()
    );
    // std::cout << "[DEBUG] RESPONSE:" << std::endl << response << std::endl << std::endl;
 
    co_await client.shutdown();

    if (response.result() != http::status::ok)
    {
        BOOST_THROW_EXCEPTION(std::logic_error("MollyBet: Invalid return code"));
    }

    std::string token = parseTokenData(
        beast::buffers_to_string(
            response.body().data()
        )
    );

    // std::cout << "[DEBUG] TOKEN: " << token << std::endl;

    co_return token;
}

std::string MollyBet::parseTokenData(const std::string& data)
{
    std::string token;
    try
    {
        auto parsedData = json::parse(data);
        if (parsedData.at("status").as_string() != "ok")
        {
            BOOST_THROW_EXCEPTION(std::logic_error("MollyBet: Invalid status"));
        }

        token = parsedData.at("data").as_string();
    }
    catch (const std::exception& e)
    {
        BOOST_THROW_EXCEPTION(std::invalid_argument(std::string("MollyBet: Invalid session JSON") + e.what()));
    }

    return token;
}

net::awaitable<void> MollyBet::runMessageLoop(const std::string& token)
{
    betting::client::WssClient client(host, port, *pContext);

    // auto res = client.do_session(host, port, "", ctx, callBack);

    std::string targetWithToken = streamTargetWithoutToken + token;
    co_await client.connect(targetWithToken);

    messageLoopStatus = mbMESSAGE_LOOP_STATE_RUN;
    while (messageLoopStatus == mbMESSAGE_LOOP_STATE_RUN) {
        // Read a message into our buffer
        beast::flat_buffer buffer = co_await client.readSimpleMessage();
        std::string sentMessage = beast::buffers_to_string(buffer.data());

        // [DEBUG]
        // std::cout << sentMessage << std::endl << std::endl;
        // std::ofstream logfile("mollybet.log", std::ofstream::app);
        // logfile << sentMessage << std::endl << std::endl;

        processSentMessage(sentMessage);
    }
    messageLoopStatus = mbMESSAGE_LOOP_STATE_NO_RUN;

    co_await client.shutdown();
}

void MollyBet::processSentMessage(const std::string& sentMessage)
{
    json::array data;
    try
    {
        data = json::parse(sentMessage).at("data").as_array();
    }
    catch (const std::exception& e)
    {
        BOOST_THROW_EXCEPTION(std::invalid_argument(std::string("MollyBet: Invalid message JSON") + e.what()));
    }

    for (auto messageJson : data)
    {
        std::pair<std::string, json::value> messagePair;
        try
        {
            auto messageArray = messageJson.as_array();
            messagePair.first = messageJson.at(0).as_string();
            if (messageArray.if_contains(1)) {
                messagePair.second = messageJson.at(1);
            }
        }
        catch (const std::exception& e)
        {
            BOOST_THROW_EXCEPTION(std::invalid_argument(std::string("MollyBet: Invalid message JSON") + e.what()));
        }

        processMessagePair(messagePair);
        if (messageLoopStatus != mbMESSAGE_LOOP_STATE_RUN)
        {
            break;
        }
    }
}

void MollyBet::processMessagePair(const std::pair<std::string, json::value>& messagePair)
{
    if (messagePair.first == mbMESSAGE_EVENT)
    {
        onMessageEvent(messagePair);
    }
    else if (messagePair.first == mbMESSAGE_SYNC)
    {
        onMessageSync(messagePair);
    }
}

void MollyBet::onMessageEvent(const std::pair<std::string, json::value>& messagePair)
{
    json::object messageContentObject;
    try
    {
        messageContentObject = messagePair.second.as_object();
    }
    catch (const std::exception& e)
    {
        BOOST_THROW_EXCEPTION(std::invalid_argument(std::string("MollyBet: Invalid event JSON") + e.what()));
    }

    if (messageContentObject.if_contains("competition_name"))
    {
        competitions.insert(messagePair.second.at("competition_name").as_string().c_str());
    }
}

void MollyBet::onMessageSync(const std::pair<std::string, json::value>& messagePair)
{
    messageLoopStatus = mbMESSAGE_LOOP_STATE_STOPPING;
}

void MollyBet::printCompetitions() const
{
    for (auto& competition : competitions) {
        std::cout << competition << std::endl;
    }
}

// const std::set<std::string>& MollyBet::getCompetitions()
// {
//     return competitions;
// }

}
