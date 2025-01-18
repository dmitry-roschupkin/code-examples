#include <algorithm>
#include <curl/curl.h>

#include <iostream>
#include <vector>
#include <string>

int main(void)
{
    curl_global_init(CURL_GLOBAL_ALL);

    std::vector<std::string> infoParts;
    auto versionInfo = curl_version_info(CURLVERSION_NOW);
    std::cout << "Current libcurl C++ library version: " << versionInfo->version << std::endl;

    auto protocols = versionInfo->protocols;
    std::cout << "Supported protocols: ";
    for (size_t i = 0; protocols[i] != NULL; i++) {
        auto protocol = static_cast<std::string>(protocols[i]);
        std::cout << protocol << " ";

        if (protocol == "http" || protocol == "https" || protocol == "ws" || protocol == "wss") {
            infoParts.push_back(protocol);
        }
    }
    std::cout << std::endl;

    std::sort(infoParts.begin(), infoParts.end(), std::greater<>());

    if (versionInfo->features & CURL_VERSION_SSL) {
        std::cout << "SSL support is present" << std::endl;
        infoParts.push_back("SSL");
    }

    if (versionInfo->features & CURL_VERSION_HTTP2) {
        std::cout << "HTTP/2 support is present" << std::endl;
        infoParts.push_back("HTTP/2");
    }

    if (versionInfo->features & CURL_VERSION_HTTP3)
        std::cout << "HTTP/3 support is present" << std::endl;

    if (versionInfo->features & CURL_VERSION_ALTSVC)
         std::cout << "Alt-svc support is present" << std::endl;


    std::string infoHash{versionInfo->version};
    std::for_each(infoParts.begin(), infoParts.end(), [&](auto& part) { infoHash += "_" + part; });
    std::cout << "INFO_HASH: " << infoHash << std::endl;

    curl_global_cleanup();

    return EXIT_SUCCESS;
}
