/*
###############################################################################
# Spike result - OK.
#
# It was rewritten from official examples by adding work with boost::asio::ssl::context
###############################################################################
*/

// #include "example/common/root_certificates.hpp"

#include <boost/asio/awaitable.hpp>
#include <boost/asio/co_spawn.hpp>
#include <boost/asio/io_context.hpp>
#include <boost/asio/ssl.hpp>
#include <boost/beast/core.hpp>
#include <boost/beast/websocket.hpp>
#include <boost/beast/websocket/ssl.hpp>

#include <cstdlib>
#include <iostream>
#include <string>
#include <fstream>

namespace beast = boost::beast;         // from <boost/beast.hpp>
namespace http = beast::http;           // from <boost/beast/http.hpp>
namespace websocket = beast::websocket; // from <boost/beast/websocket.hpp>
namespace net = boost::asio;            // from <boost/asio.hpp>
namespace ssl = boost::asio::ssl;       // from <boost/asio/ssl.hpp>

using tcp = boost::asio::ip::tcp;       // from <boost/asio/ip/tcp.hpp>


// Sends a WebSocket message and prints the response
net::awaitable<void>
do_session(std::string host, std::string port, std::string text, ssl::context& ctx)
{
    auto executor = co_await net::this_coro::executor;
    auto resolver = tcp::resolver{ executor };
    //auto stream = websocket::stream<beast::tcp_stream>{ executor };
    auto stream = websocket::stream<ssl::stream<beast::tcp_stream>>(executor, ctx);

    // Look up the domain name
    auto const results = co_await resolver.async_resolve(host, port);

    // Set a timeout on the operation
    beast::get_lowest_layer(stream).expires_after(std::chrono::seconds(30));

    // Make the connection on the IP address we get from a lookup
    auto ep = co_await beast::get_lowest_layer(stream).async_connect(results);

    // Set SNI Hostname (many hosts need this to handshake successfully)
    if (!SSL_set_tlsext_host_name(
        stream.next_layer().native_handle(),
        host.c_str()))
    {
        throw boost::system::system_error(
            static_cast<int>(::ERR_get_error()),
            net::error::get_ssl_category());
    }


    // Update the host_ string. This will provide the value of the
    // Host HTTP header during the WebSocket handshake.
    // See https://tools.ietf.org/html/rfc7230#section-5.4
    host += ':' + std::to_string(ep.port());

    // Set a timeout on the operation
    beast::get_lowest_layer(stream).expires_after(std::chrono::seconds(30));

    // Set a decorator to change the User-Agent of the handshake
    stream.set_option(websocket::stream_base::decorator(
        [](websocket::request_type& req)
        {
            req.set(http::field::user_agent,
                std::string(BOOST_BEAST_VERSION_STRING) +
                " websocket-client-coro");
        }));

    // Perform the SSL handshake
    co_await stream.next_layer().async_handshake(ssl::stream_base::client);

    // Turn off the timeout on the tcp_stream, because
    // the websocket stream has its own timeout system.
    beast::get_lowest_layer(stream).expires_never();

    // Set suggested timeout settings for the websocket
    stream.set_option(
        websocket::stream_base::timeout::suggested(beast::role_type::client));

    // Perform the websocket handshake
    // co_await stream.async_handshake(host, "/");
    co_await stream.async_handshake(host, "/v1/stream/?token=5581c274f2efa377405656fde934d81d");

    // Send the message
    // co_await stream.async_write(net::buffer(text));

    // This buffer will hold the incoming message
    
    std::ofstream logfile("mollybet.log");
    std::ofstream logfileSize("mollybet-size.log");
    while (true) {
        beast::flat_buffer buffer;
        // Read a message into our buffer
        co_await stream.async_read(buffer);

        // The make_printable() function helps print a ConstBufferSequence
        std::cout << beast::make_printable(buffer.data()) << std::endl << buffer.size() << std::endl << std::endl;
        logfile << beast::make_printable(buffer.data()) << std::endl << buffer.size() << std::endl << std::endl;
        logfileSize << buffer.size() << std::endl;
    }

    // Close the WebSocket connection
    co_await stream.async_close(websocket::close_code::normal);

    // If we get here then the connection is closed gracefully
}

//------------------------------------------------------------------------------

int
main(int argc, char** argv)
{
    try
    {
        auto const host = "api.mollybet.com";
        auto const port = "443";
        auto const target = "/v1/sessions/";
        auto const text = "test";

        // The io_context is required for all I/O
        net::io_context ioc;

        // The SSL context is required, and holds certificates
        ssl::context ctx{ ssl::context::tlsv12_client };

        // This holds the root certificate used for verification
        // load_root_certificates(ctx);

        // Launch the asynchronous operation
        net::co_spawn(
            ioc,
            do_session(host, port, text, ctx),
            [](std::exception_ptr e)
            {
                if (e)
                    std::rethrow_exception(e);
            });

        // Run the I/O service. The call will return when
        // the socket is closed.
        ioc.run();
    }
    catch (std::exception const& e)
    {
        std::cerr << "Error: " << e.what() << std::endl;
        return EXIT_FAILURE;
    }
    return EXIT_SUCCESS;
}
