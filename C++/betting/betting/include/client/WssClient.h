#pragma once

#include <boost/asio.hpp>
#include <boost/asio/ssl.hpp>
#include <boost/asio/awaitable.hpp>
#include <boost/beast/core.hpp>
#include <boost/beast/websocket.hpp>

namespace beast = boost::beast;
namespace ssl = boost::asio::ssl;
namespace net = boost::asio;
namespace websocket = beast::websocket;

namespace betting::client
{

class WssClient
{
public:
    explicit WssClient(std::string_view host, std::string_view port, ssl::context& ctx);

    virtual ~WssClient() = default;

    // Connect
    net::awaitable<void> connect(std::string_view target);

    // Send simple websocket message
    net::awaitable<beast::flat_buffer> readSimpleMessage();
    // if we will need to work with non typical / large messages - we can make separate function or at
    // least return stream outside, as it described in HttpsClient, under readSimpleResponse function
    // For current task we don't need it, that's why according "YAGNI" principle it not implemented here

    // Close the stream
    net::awaitable<void> shutdown();

private:
    std::string_view host;
    std::string_view port;
    ssl::context* pContext;

    std::unique_ptr<websocket::stream<ssl::stream<beast::tcp_stream>>> pStream;
};

}
