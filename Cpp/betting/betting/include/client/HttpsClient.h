#pragma once

#include <boost/asio.hpp>
#include <boost/asio/ssl.hpp>
#include <boost/asio/awaitable.hpp>
#include <boost/beast/core.hpp>
#include <boost/beast/http.hpp>

#include <string>
#include <memory> 

namespace beast = boost::beast;
namespace ssl = boost::asio::ssl;
namespace net = boost::asio;
namespace http = beast::http;

namespace betting::client
{

class HttpsClient
{
public:
    explicit HttpsClient(std::string_view host, std::string_view port, ssl::context& ctx);

    virtual ~HttpsClient() = default;

    // Connect
    net::awaitable<void> connect();

    // Send simple https request
    net::awaitable<http::request<http::string_body>> sendSimpleRequest(
        http::verb method, std::string_view target, std::string_view body = "", std::string_view contentType = "", int version = 11
    );
    // To make requests with e.g. large body you can use getStream() function and the use stream directly or make a new method in this class
    // and use boost parsers and buffer
    // For current task we don't need it, that's why according "YAGNI" principle it not implemented here

    // Send simple https response
    net::awaitable<http::response<http::dynamic_body>> readSimpleResponse();
    // To make requests with e.g. large body you can use getStream() function and the use stream directly or make a new method in this class
    // and use boost parsers and buffer, e.g.:
    //
    // beast::flat_buffer buffer;
    // boost::system::error_code ec
    // parser<isRequest, buffer_body> p;
    // read_header(stream, buffer, p, ec);
    // if (ec)
    //     return;
    // while (!p.is_done())
    // {
    //     char buf[512];
    //     p.get().body().data = buf;
    //      p.get().body().size = sizeof(buf);
    //     read(stream, buffer, p, ec);
    //     if (ec == error::need_buffer)
    //         ec = {};
    //     if (ec)
    //         return;
    //     os.write(buf, sizeof(buf) - p.get().body().size);
    // }
    //
    // For current task we don't need it, that's why according "YAGNI" principle it not implemented here

    // Return stream to give possibility make some operation outside.
    ssl::stream<beast::tcp_stream>& getStream();

    // Close the stream
    net::awaitable<void> shutdown();

private:
    std::string_view host;
    std::string_view port;
    ssl::context* pContext;

    std::unique_ptr<ssl::stream<beast::tcp_stream>> pStream;
};

}
