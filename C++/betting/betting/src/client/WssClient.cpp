#include "client/WssClient.h"

#include <boost/beast/http.hpp>
#include <boost/beast/version.hpp>
#include <boost/beast/websocket/ssl.hpp>

#include <iostream>

namespace http = beast::http;

namespace betting::client
{

WssClient::WssClient(std::string_view host, std::string_view port, ssl::context& ctx) :
    host(host),
    port(port),
    pContext(&ctx)
{
}

net::awaitable<void> WssClient::connect(std::string_view target)
{
    auto executor = co_await net::this_coro::executor;
    auto resolver = net::ip::tcp::resolver{ executor };
    pStream = std::make_unique<websocket::stream<ssl::stream<beast::tcp_stream>>>(executor, *pContext);

    // Look up the domain name
    auto const results = co_await resolver.async_resolve(host, port);

    // Set a timeout on the operation
    beast::get_lowest_layer(*pStream).expires_after(std::chrono::seconds(30));

    // Make the connection on the IP address we get from a lookup
    auto ep = co_await beast::get_lowest_layer(*pStream).async_connect(results);

    // Set SNI Hostname (many hosts need this to handshake successfully)
    if (!SSL_set_tlsext_host_name(
        pStream->next_layer().native_handle(),
        host.data()))
    {
        throw ::boost::system::system_error(
            static_cast<int>(::ERR_get_error()),
            net::error::get_ssl_category());
    }


    // Update the host_ string. This will provide the value of the
    // Host HTTP header during the WebSocket handshake.
    // See https://tools.ietf.org/html/rfc7230#section-5.4
    std::string hostWithPort = std::string(host) + ':' + std::to_string(ep.port());

    // Set a timeout on the operation
    beast::get_lowest_layer(*pStream).expires_after(std::chrono::seconds(30));

    // Set a decorator to change the User-Agent of the handshake
    pStream->set_option(websocket::stream_base::decorator(
        [](websocket::request_type& req)
        {
            req.set(http::field::user_agent,
                std::string(BOOST_BEAST_VERSION_STRING) +
                " websocket-client-coro");
        }));

    // Perform the SSL handshake
    co_await pStream->next_layer().async_handshake(ssl::stream_base::client);

    // Turn off the timeout on the tcp_stream, because
    // the websocket stream has its own timeout system.
    beast::get_lowest_layer(*pStream).expires_never();

    // Set suggested timeout settings for the websocket
    pStream->set_option(
        websocket::stream_base::timeout::suggested(beast::role_type::client));

    // Perform the websocket handshake
    co_await pStream->async_handshake(hostWithPort, target);
}

net::awaitable<beast::flat_buffer> WssClient::readSimpleMessage()
{
    // Read a message into our buffer
    beast::flat_buffer buffer;
    co_await pStream->async_read(buffer);

    co_return buffer;
}

net::awaitable<void> WssClient::shutdown()
{
    // Close the WebSocket connection
    co_await pStream->async_close(websocket::close_code::normal);
    // If we get here then the connection is closed gracefully
}

}

