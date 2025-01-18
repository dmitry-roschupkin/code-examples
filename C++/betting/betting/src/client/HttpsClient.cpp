#include "client/HttpsClient.h"

#include <boost/asio/as_tuple.hpp>
#include <boost/beast/version.hpp>

#include <iostream>

namespace betting::client
{

HttpsClient::HttpsClient(std::string_view host, std::string_view port, ssl::context& ctx):
    host(host),
    port(port),
    pContext(&ctx)
{
}

net::awaitable<void> HttpsClient::connect()
{
    const auto &executor = co_await net::this_coro::executor;
    auto resolver = net::ip::tcp::resolver{ executor };
    pStream = std::make_unique<ssl::stream<beast::tcp_stream>>(executor, *pContext);

    // Set SNI Hostname (many hosts need this to handshake successfully)
    if (!SSL_set_tlsext_host_name(pStream->native_handle(), host.data()))
    {
        throw ::boost::system::system_error(
            static_cast<int>(::ERR_get_error()),
            net::error::get_ssl_category());
    }

    // Look up the domain name
    auto const results = co_await resolver.async_resolve(host, port);

    // Set the timeout.
    beast::get_lowest_layer(*pStream).expires_after(std::chrono::seconds(30));

    // Make the connection on the IP address we get from a lookup
    co_await beast::get_lowest_layer(*pStream).async_connect(results);

    // Set the timeout.
    beast::get_lowest_layer(*pStream).expires_after(std::chrono::seconds(30));

    // Perform the SSL handshake
    co_await pStream->async_handshake(ssl::stream_base::client);
}

net::awaitable<http::request<http::string_body>> HttpsClient::sendSimpleRequest(
    http::verb method, std::string_view target, std::string_view body, std::string_view contentType, int version
)
{
    // Set up an HTTP request message
    http::request<http::string_body> request{ method, target, version };
    request.set(http::field::host, host);
    request.set(http::field::user_agent, BOOST_BEAST_VERSION_STRING);

    if (!body.empty())
    {
        request.body() = body;
        request.prepare_payload();
    }

    if (!contentType.empty())
    {
        request.set(http::field::content_type, contentType);
    }

    //std::cout << "[DEBUG] REQUEST:" << std::endl << request << std::endl << std::endl; 

    // Set the timeout.
    beast::get_lowest_layer(*pStream).expires_after(std::chrono::seconds(30));

    // Send the HTTP request to the remote host
    co_await http::async_write(*pStream, request);

    co_return request;
}

net::awaitable<http::response<http::dynamic_body>> HttpsClient::readSimpleResponse()
{
    // This buffer is used for reading and must be persisted
    beast::flat_buffer buffer;

    // Declare a container to hold the response
    http::response<http::dynamic_body> response;

    // Receive the HTTP response
    co_await http::async_read(*pStream, buffer, response);

    co_return response;
}

ssl::stream<beast::tcp_stream>& HttpsClient::getStream()
{
    return *pStream;
}

net::awaitable<void> HttpsClient::shutdown()
{
    // Set the timeout.
    beast::get_lowest_layer(*pStream).expires_after(std::chrono::seconds(30));

    // Gracefully close the stream - do not threat every error as an exception!
    auto [ec] = co_await pStream->async_shutdown(net::as_tuple);

    // ssl::error::stream_truncated, also known as an SSL "short read",
    // indicates the peer closed the connection without performing the
    // required closing handshake (for example, Google does this to
    // improve performance). Generally this can be a security issue,
    // but if your communication protocol is self-terminated (as
    // it is with both HTTP and WebSocket) then you may simply
    // ignore the lack of close_notify.
    //
    // https://github.com/boostorg/beast/issues/38
    //
    // https://security.stackexchange.com/questions/91435/how-to-handle-a-malicious-ssl-tls-shutdown
    //
    // When a short read would cut off the end of an HTTP message,
    // Beast returns the error beast::http::error::partial_message.
    // Therefore, if we see a short read here, it has occurred
    // after the message has been completed, so it is safe to ignore it.

    if (ec && ec != net::ssl::error::stream_truncated)
        throw ::boost::system::system_error(ec, "shutdown");
}

}
