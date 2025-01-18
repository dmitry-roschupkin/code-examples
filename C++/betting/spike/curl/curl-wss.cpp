/*
###############################################################################
# Spike result - FAIL.
#
# Problem to detect end of ws message, ## see FIXME comment bellow
# for more details
# 
# On libcurl official site we can see the the message that curlib-ws
# is experimental:
# https://curl.se/libcurl/c/libcurl-ws.html
#
# libcurl is a widely used and proven library used by many large companies
# including IBM, but with other protocols, not with websockets.
#
###############################################################################
*/

#include <stdio.h>
#include <iostream>
#include <ostream>
#include <curl/curl.h>

struct wsData
{
    CURL* curl;
    std::string buffer;
};

static size_t writecb(char* b, size_t size, size_t nitems, void* p)
{
    const unsigned int real_size = nitems * size;
    const auto data = static_cast<wsData*>(p);

    const struct curl_ws_frame* frame = curl_ws_meta(data->curl);

    std::cout << "flag: " << frame->flags << std::endl;
    std::cout << "bytesleft: " << frame->bytesleft << std::endl;
    std::cout << "offset: " << frame->offset << std::endl;
    std::cout << "nitems: " << nitems << std::endl;
    std::cout << "size: " << size << std::endl;
    std::cout << "bytes: " << real_size << std::endl;
    std::cout << "age: " << frame->age << std::endl;

    if (frame->flags == CURLWS_CONT || frame->offset > 0) {
        std::cout << "####### contunue previos massage, new fragment #######" << std::endl;
    }
    else {
        //TODO: call onTakeMessage here
        data->buffer.clear();
    }

    //FIXME: Can't solve next Problem: In general I can't understand is this final frame/part or not
    // In general no difference between last message frame/part and penultimate message frame/part
    //Example:
    /*  
        ### Penultimate message frame/part: ####

        flag: 4
        bytesleft: 0
        offset: 0
        nitems: 4096
        size: 1
        age: 0
        bytes: 4096
        ####### contunue previos massage, new fragment #######
        tion_id":545,"competition_name":"USA NFL","competition_country":"US","home":"Los Angeles Rams","away":"Philadelphia Eagles","event_name":"Los Angeles Rams vs. Philadelphia Eagles","ir_status":"pre_event","start_time":"2024-11-25T01:20:00Z"}],["event",{"event_type":"normal","sport":"af","event_id":"2024-11-26,91763,21616","competition_id":545,"competition_name":"USA NFL","competition_country":"US","home":"Los Angeles Chargers","away":"Baltimore Ravens","event_name":"Los Angeles Chargers vs. Baltimore Ravens","ir_status":"pre_event","start_time":"2024-11-26T01:15:00Z"}],["event",{"event_type":"normal","sport":"af","event_id":"2024-11-28,21622,21634","competition_id":545,"competition_name":"USA NFL","competition_country":"US","home":"Dallas Cowboys","away":"New York Giants","event_name":"Dallas Cowboys vs. New York Giants","ir_status":"pre_event","start_time":"2024-11-28T21:30:00Z"}],["event",{"event_type":"normal","sport":"af","event_id":"2024-11-28,21624,21619","competition_id":545,"competition_name":"USA NFL","competition_country":"US","home":"Detroit Lions","away":"Chicago Bears","event_name":"Detroit Lions vs. Chicago Bears","ir_status":"pre_event","start_time":"2024-11-28T17:30:00Z"}],["event",{"event_type":"normal","sport":"af","event_id":"2024-11-29,21625,21630","competition_id":545,"competition_name":"USA NFL","competition_country":"US","home":"Green Bay Packers","away":"Miami Dolphins","event_name":"Green Bay Packers vs. Miami Dolphins","ir_status":"pre_event","start_time":"2024-11-29T01:20:00Z"}],["event",{"event_type":"normal","sport":"af","event_id":"2024-11-29,21629,21636","competition_id":545,"competition_name":"USA NFL","competition_country":"US","home":"Kansas City Chiefs","away":"Las Vegas Raiders","event_name":"Kansas City Chiefs vs. Las Vegas Raiders","ir_status":"pre_event","start_time":"2024-11-29T20:00:00Z"}],["event",{"event_type":"normal","sport":"af","event_id":"2024-11-30,22436,22524","competition_id":546,"competition_name":"USA NCAA","competition_country":"US","home":"Florida State Seminoles","away":"Florida Gators","event_name":"Florida State Seminoles vs. Florida Gators","ir_status":"pre_event","start_time":"2024-11-30T18:00:00Z"}],["event",{"event_type":"normal","sport":"af","event_id":"2024-11-30,22439,22440","competition_id":546,"competition_name":"USA NCAA","competition_country":"US","home":"Wake Forest Demon Deacons","away":"Duke Blue Devils","event_name":"Wake Forest Demon Deacons vs. Duke Blue Devils","ir_status":"pre_event","start_time":"2024-11-30T17:00:00Z"}],["event",{"event_type":"normal","sport":"af","event_id":"2024-11-30,22443,22438","competition_id":546,"competition_name":"USA NCAA","competition_country":"US","home":"North Carolina Tar Heels","away":"North Carolina State Wolfpack","event_name":"North Carolina Tar Heels vs. North Carolina State Wolfpack","ir_status":"pre_event","start_time":"2024-11-30T20:30:00Z"}],["event",{"event_type":"normal","sport":"af","event_id":"2024-11-30,22461,22457","competition_id":546,"competition_name":"USA NCAA","competition_country":"US","home":"Ohio State Buckeyes","away":"Michigan Wolverines","event_name":"Ohio State Buckeyes vs. Michigan Wolverines","ir_status":"pre_event","start_time":"2024-11-30T17:00:00Z"}],["event",{"event_type":"normal","sport":"af","event_id":"2024-11-30,22517,22522","competition_id":546,"competition_name":"USA NCAA","competition_country":"US","home":"Oregon Ducks","away":"Washington Huskies","event_name":"Oregon Ducks vs. Washington Huskies","ir_status":"pre_event","start_time":"2024-11-30T18:00:00Z"}],["event",{"event_type":"normal","sport":"af","event_id":"2024-11-30,22521,22491","competition_id":546,"competition_name":"USA NCAA","competition_country":"US","home":"USC Trojans","away":"Notre Dame Fighting Irish","event_name":"USC Trojans vs. Notre Dame Fighting Irish","ir_status":"pre_event","start_time":"2024-11-30T18:00:00Z"}],["event",{"event_type":"normal","sport":"af","event_id":"2024-11-30,22530,22532","competition_id":546,"competition_name":"USA NCAA","competition_country":"US","home":"Alabama Crimson Tide","awa
        --------------------------------------------------------------------

        ### Last message frame/part: ####

        flag: 4
        bytesleft: 0
        offset: 0
        nitems: 136
        size: 1
        age: 0
        bytes: 136
        ####### contunue previos massage, new fragment #######
        y":"Auburn Tigers","event_name":"Alabama Crimson Tide vs. Auburn Tigers","ir_status":"pre_event","start_time":"2024-11-30T20:30:00Z"}]]}
        --------------------------------------------------------------------

        ### New message (first new message frame/part): ####

        flag: 1
        bytesleft: 0
        offset: 0
        size: 1
        nitems: 4096
        age: 0
        bytes: 4096
        {"ts":1732064261.452812,"data":[["event",{"event_type":"normal","sport":"af","event_id":"2024-12-01,10050390,21644","competition_id":545,"competition_name":"USA NFL","competition_country":"US","home":"Washington Commanders","away":"Tennessee Titans","event_name":"Washington Commanders vs. Tennessee Titans","ir_status":"pre_event","start_time":"2024-12-01T18:00:00Z"}],["event",{"event_type":"normal","sport":"af","event_id":"2024-12-01,21615,91763","competition_id":545,"competition_name":"USA NFL","competition_country":"US","home":"Atlanta Falcons","away":"Los Angeles Chargers","event_name":"Atlanta Falcons vs. Los Angeles Chargers","ir_status":"pre_event","start_time":"2024-12-01T18:00:00Z"}],["event",{"event_type":"normal","sport":"af","event_id":"2024-12-01,21616,21637","competition_id":545,"competition_name":"USA NFL","competition_country":"US","home":"Baltimore Ravens","away":"Philadelphia Eagles","event_name":"Baltimore Ravens vs. Philadelphia Eagles","ir_status":"pre_event","start_time":"2024-12-01T21:25:00Z"}],["event",{"event_type":"normal","sport":"af","event_id":"2024-12-01,21618,21643","competition_id":545,"competition_name":"USA NFL","competition_country":"US","home":"Carolina Panthers","away":"Tampa Bay Buccaneers","event_name":"Carolina Panthers vs. Tampa Bay Buccaneers","ir_status":"pre_event","start_time":"2024-12-01T21:05:00Z"}],["event",{"event_type":"normal","sport":"af","event_id":"2024-12-01,21620,21638","competition_id":545,"competition_name":"USA NFL","competition_country":"US","home":"Cincinnati Bengals","away":"Pittsburgh Steelers","event_name":"Cincinnati Bengals vs. Pittsburgh Steelers","ir_status":"pre_event","start_time":"2024-12-01T18:00:00Z"}],["event",{"event_type":"normal","sport":"af","event_id":"2024-12-01,21628,21626","competition_id":545,"competition_name":"USA NFL","competition_country":"US","home":"Jacksonville Jaguars","away":"Houston Texans","event_name":"Jacksonville Jaguars vs. Houston Texans","ir_status":"pre_event","start_time":"2024-12-01T18:00:00Z"}],["event",{"event_type":"normal","sport":"af","event_id":"2024-12-01,21631,21614","competition_id":545,"competition_name":"USA NFL","competition_country":"US","home":"Minnesota Vikings","away":"Arizona Cardinals","event_name":"Minnesota Vikings vs. Arizona Cardinals","ir_status":"pre_event","start_time":"2024-12-01T18:00:00Z"}],["event",{"event_type":"normal","sport":"af","event_id":"2024-12-01,21632,21627","competition_id":545,"competition_name":"USA NFL","competition_country":"US","home":"New England Patriots","away":"Indianapolis Colts","event_name":"New England Patriots vs. Indianapolis Colts","ir_status":"pre_event","start_time":"2024-12-01T18:00:00Z"}],["event",{"event_type":"normal","sport":"af","event_id":"2024-12-01,21633,79446","competition_id":545,"competition_name":"USA NFL","competition_country":"US","home":"New Orleans Saints","away":"Los Angeles Rams","event_name":"New Orleans Saints vs. Los Angeles Rams","ir_status":"pre_event","start_time":"2024-12-01T21:05:00Z"}],["event",{"event_type":"normal","sport":"af","event_id":"2024-12-01,21635,21641","competition_id":545,"competition_name":"USA NFL","competition_country":"US","home":"New York Jets","away":"Seattle Seahawks","event_name":"New York Jets vs. Seattle Seahawks","ir_status":"pre_event","start_time":"2024-12-01T18:00:00Z"}],["event",{"event_type":"normal","sport":"af","event_id":"2024-12-01,22475,22474","competition_id":546,"competition_name":"USA NCAA","competition_country":"US","home":"Texas A&M Aggies","away":"Texas Longhorns","event_name":"Texas A&M Aggies vs. Texas Longhorns","ir_status":"pre_event","start_time":"2024-12-01T00:30:00Z"}],["event",{"event_type":"normal","sport":"af","event_id":"2024-12-01,22533,22472","competition_id":546,"competition_name":"USA NCAA","competition_country":"US","home":"LSU Tigers","away":"Oklahoma Sooners","event_name":"LSU Tigers vs. Oklahoma Sooners","ir_status":"pre_event","start_time":"2024-12-01T00:00:00Z"}],["event",{"event_type":"normal","sport":"af","event_id":"2024-12-02,21617,21640","competition_id":545,"competitio
    */

    data->buffer.append(b, real_size);
    std::cout << data->buffer << std::endl;

    std::cout << "--------------------------------------------------------------------" << std::endl;

    return real_size;
}

int main(void)
{
    auto curl = curl_easy_init();
    wsData data{ .curl=curl };

    if (curl) {
        //curl_easy_setopt(curl, CURLOPT_URL, "wss://example.com");
        curl_easy_setopt(curl, CURLOPT_URL, "wss://api.mollybet.com/v1/stream?token=7ba793fa617691d66fd8a8581a8e1419");

        curl_easy_setopt(curl, CURLOPT_WRITEFUNCTION, writecb);
        /* pass the easy handle to the callback */
        curl_easy_setopt(curl, CURLOPT_WRITEDATA, &data);

        /* Perform the request, res gets the return code */
        auto res = curl_easy_perform(curl);
        /* Check for errors */
        if (res != CURLE_OK)
            fprintf(stderr, "curl_easy_perform() failed: %s\n",
                curl_easy_strerror(res));

        /* always cleanup */
        curl_easy_cleanup(curl);
    }
    return 0;
}
