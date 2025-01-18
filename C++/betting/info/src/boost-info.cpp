#include <boost/version.hpp>

#include <iostream>

int main()
{
    std::cout << "Current boost C++ libraries version: " << BOOST_VERSION << std::endl;
    std::cout << "INFO_HASH: " << BOOST_VERSION << std::endl;

    return EXIT_SUCCESS;
}
