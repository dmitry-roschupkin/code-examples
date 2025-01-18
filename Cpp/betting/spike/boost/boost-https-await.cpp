/*
###############################################################################
# Spike result - OK.
###############################################################################
*/

#include <boost/asio/as_tuple.hpp>
#include <boost/asio/awaitable.hpp>
#include <boost/asio/co_spawn.hpp>
#include <boost/asio/io_context.hpp>
#include <boost/asio/ssl.hpp>
#include <boost/beast/core.hpp>
#include <boost/beast/http.hpp>
#include <boost/beast/version.hpp>
#include <boost/beast/core/buffers_to_string.hpp>

// #include "example/common/root_certificates.hpp"

#include <cstdlib>
#include <iostream>
#include <string>

namespace beast = boost::beast;
namespace http = beast::http;
namespace net = boost::asio;
namespace ssl = boost::asio::ssl;

// Performs an HTTP POST and prints the response
net::awaitable<void>
do_session(
    std::string host,
    std::string port,
    std::string target,
    int version,
    ssl::context& ctx)
{
    auto executor = co_await net::this_coro::executor;
    auto resolver = net::ip::tcp::resolver{ executor };
    auto stream = ssl::stream<beast::tcp_stream>{ executor, ctx };

    // Set SNI Hostname (many hosts need this to handshake successfully)
    if (!SSL_set_tlsext_host_name(stream.native_handle(), host.c_str()))
    {
        throw boost::system::system_error(
            static_cast<int>(::ERR_get_error()),
            net::error::get_ssl_category());
    }

    // Look up the domain name
    auto const results = co_await resolver.async_resolve(host, port);

    // Set the timeout.
    beast::get_lowest_layer(stream).expires_after(std::chrono::seconds(30));

    // Make the connection on the IP address we get from a lookup
    co_await beast::get_lowest_layer(stream).async_connect(results);

    // Set the timeout.
    beast::get_lowest_layer(stream).expires_after(std::chrono::seconds(30));

    // Perform the SSL handshake
    co_await stream.async_handshake(ssl::stream_base::client);

    // Set up an HTTP POST request message
    http::request<http::string_body> req{ http::verb::post, target, version };
    req.set(http::field::host, host);
    req.set(http::field::user_agent, BOOST_BEAST_VERSION_STRING);

    req.set(http::field::content_type, "application/json");
    req.body() = R"({"username":"devinterview", "password":"OwAb6wrocirEv"})";
    req.prepare_payload();
    std::cout << "REQUEST:" << std::endl << req << std::endl << std::endl;

    // Set the timeout.
    beast::get_lowest_layer(stream).expires_after(std::chrono::seconds(30));

    // Send the HTTP request to the remote host
    co_await http::async_write(stream, req);

    // This buffer is used for reading and must be persisted
    beast::flat_buffer buffer;

    // Declare a container to hold the response
    http::response<http::dynamic_body> res;

    // Receive the HTTP response

    //http::error::end_of_stream
    /*char buf[16];
    res.body().data = buf;
    res.body().size = sizeof(buf);*/

    co_await http::async_read(stream, buffer, res);

    // Write the message to standard out
    std::cout << "RESPONSE:" << std::endl << res << std::endl << std::endl;
    std::cout << "RESPONSE BODY:" << std::endl << beast::buffers_to_string(res.body().data()) << std::endl;

    // Set the timeout.
    beast::get_lowest_layer(stream).expires_after(std::chrono::seconds(30));

    // Gracefully close the stream - do not threat every error as an exception!
    auto [ec] = co_await stream.async_shutdown(net::as_tuple);

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
        throw boost::system::system_error(ec, "shutdown");
}

//------------------------------------------------------------------------------

int
main()
{
    try
    {
        auto const host = "api.mollybet.com";
        auto const port = "443";
        auto const target = "/v1/sessions/";
        int version = 11;

        /*auto const host = "archives.boost.io";
        auto const port = "443";
        auto const target = "/release/1.86.0/source/boost_1_86_0.tar.gz";
        int version = 11;*/
                
        // The io_context is required for all I/O
        net::io_context ioc;

        // The SSL context is required, and holds certificates
        ssl::context ctx{ ssl::context::tlsv12_client };

        // This holds the root certificate used for verification
        // load_root_certificates(ctx);

        // Verify the remote server's certificate
        ctx.set_verify_mode(ssl::verify_none);

        // Launch the asynchronous operation
        net::co_spawn(
            ioc,
            do_session(host, port, target, version, ctx),
            // If the awaitable exists with an exception, it gets delivered here
            // as `e`. This can happen for regular errors, such as connection
            // drops.
            [](std::exception_ptr e)
            {
                if (e)
                    std::rethrow_exception(e);
            });

        // Run the I/O service. The call will return when
        // the operation is complete.
        ioc.run();
    }
    catch (std::exception const& e)
    {
        std::cerr << "Error: " << e.what() << std::endl;
        return EXIT_FAILURE;
    }
    return EXIT_SUCCESS;
}

