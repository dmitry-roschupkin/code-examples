/*
###############################################################################
# Spike result - OK.
# 
# libcurl is a widely used and proven library used by many large companies
# including IBM. Supporting HTTP/2 and can be compiled
# with HTTP/3 (experimental mode)
# 
# libcurl can be good chose for HTTP client
###############################################################################
*/

#include <iostream>
#include <ostream>
#include <stdio.h>
#include <curl/curl.h>

#include <nlohmann/json.hpp>

using json = nlohmann::json;

static size_t cb(char* data, size_t size, size_t nmemb, void* clientp)
{
    auto r = static_cast<std::string*>(clientp);
    const unsigned int real_size = size * nmemb;
    r->append(data, real_size);
    return real_size;
}

int main(void)
{
    std::string buffer;
    CURLcode res;
    CURL* curl = curl_easy_init();
    json data;

    if (curl) {
        curl_easy_setopt(curl, CURLOPT_URL, "https://api.mollybet.com/v1/sessions/");

        /* send all data to this function  */
        curl_easy_setopt(curl, CURLOPT_WRITEFUNCTION, cb);

        /* we pass our 'chunk' struct to the callback function */
        curl_easy_setopt(curl, CURLOPT_WRITEDATA, (void*)&buffer);

        curl_easy_setopt(curl, CURLOPT_POSTFIELDS, "username=devinterview&password=OwAb6wrocirEv");

        /* send a request */
        res = curl_easy_perform(curl);

        if (CURLE_OK == res) {
            std::cout << buffer << std::endl;

            data = json::parse(buffer);
            std::cout << "STATUS:" << data["status"] << std::endl;
            std::cout << "DATA:" << data["data"] << std::endl;

            if (!data.contains("no_exist")) {
                std::cout << "Not contains no_exist" << std::endl;
            }

           //printf("We received Content-Type: %s\n", ct);
        } else {
            printf("Http request error");
        }

        curl_easy_cleanup(curl);
    }

    return 0;
}
