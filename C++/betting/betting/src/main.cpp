#include "mollybet/MollyBet.h"

#include <boost/asio/co_spawn.hpp>
#include <boost/exception/diagnostic_information.hpp>

#include <cstdlib>
#include <iostream>

namespace net = boost::asio;

int main()
{
    try
    {
        // The io_context is required for all I/O
        net::io_context ioc;

        // The SSL context is required, and holds certificates.
        ssl::context ctx{ ssl::context::tlsv12_client };

        // This holds the root certificate used for verification
        // load_root_certificates(ctx);

        // Verify the remote server's certificate
        ctx.set_verify_mode(ssl::verify_none);

        betting::mollybet::MollyBet mollyBet(ctx);

        // Launch the asynchronous operation
        net::co_spawn(
            ioc,
            [&mollyBet]() -> net::awaitable<void>
            {
                auto token = co_await mollyBet.takeToken();
                co_await mollyBet.runMessageLoop(token);
            },
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

        mollyBet.printCompetitions();
    }
    catch (std::exception const& e)
    {
        std::cerr << "Error: " << e.what() << std::endl << boost::diagnostic_information(e) << std::endl;
        return EXIT_FAILURE;
    }

    return EXIT_SUCCESS;
}
