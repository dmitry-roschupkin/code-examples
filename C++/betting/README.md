## Task
The requirements are as follows:

1. Make an HTTP request to login to [Molly API](https://api.mollybet.com/docs/). You can use the test credentials username=*** password=*** to get back a session token.
2. Connect a websocket to the Molly API stream.
3. Read messages up until the "sync" message.
4. Disconnect the websocket, and print out the distinct "competition_name" values seen in "event" messages your received.

Please include a self-contained Dockerfile that fetches libraries and compiles your code so we can test it, and also a readme file that explains the structure and decisions in your code.

## Run
To build docker need to execute command: 
```shell script
docker build -t betting .
```

To run docker need to execute command:
```shell script
docker run -it --rm betting
```
or, if you need bash you can execute command:   
```shell script
docker run -it --rm betting /bin/bash
```

## About
For this task next libraries were researched:  
   - `libcurl`
   - `websocketpp`
   - `boost`
   - `libwebsokets`

You can see some drafts in the folder `spike`. You can see some conclusions in each `spike` test file.
I didn't make spike using `libwebsokets`, it's very low level and need to have good argumentation to use it, 
because need to write and then to support much more code.

I haven't work with `boost` a lot, but looks like it's good choice for this task. I chose `boost` for this task. 


## Structure
`main.cpp` - file with main function and main coroutine lambda function 
`client` - folder for http ans websocket clients implementation  
`mollybet` - folder with MollyBet logic implementation  

Also, you can read comments in files.  
  
All MollyBet connection data hardcoded in `MollyBet.h`  

 `MollyBet.h` is located in separate folder and namespace. This structure allow as to add process other betting 
 websites if we will need.
 
SSL certificate checking disabled now. Need to have root certificates to enable it, or we can use default boost/openssl
root certificates, but in this case - no any warranty that they are fresh and actual.


## Ideas about project and next iterations/sprints :)

1. To make this application more structured and flexible need move all connection data to config file.
It can have JSON format like this:
    ```json
    {
      "MollyBet": {
        "username": "username_value",
        "passwors": "password_value",
        ...
      },
      "OtherBet": {
        ...
      }
      ...
    }
    ```
    I can make common `singleton Config` class to read the config file and to storing the data there. We can make
additionally classes for each betting website with function `parse` and with needed public members and create
a member of each class (or std::map<>) inside the `singleton Config` class. In this case we will have useful
and structured configuration data, and each betting side namespace will have own responsibilities about parsing
and storing it config data.

2. I can move `ssl::context ctx` to separate singleton class

3. I can enable SSL certificate checking

4. Possible to create separate class/structure for MollyBet websocket message and work with messages as with objects.
E.g.
      - common class for general message, with function `parse(std::string)` with field `ts` and `data` (vector of pair);
      - class for `event` with function `parse(std::pair)` and with fields `sport, event_id, event_name, ...` and pass
objects (not JSON) to function onMessageEvent
      - class for `sync` with function `parse(std::pair)` and with field `token`
      - ...

These four points will take 7+/- hours, but I can't do more than 3-4 hours per day now, so it would be move this task
deadline for a couple days +/-.

- Possible to create another implementation of http or/and websocket clients e.g. using `libcurl` with supporting http/3.
For this we need to make interface for each client and make this interface implementation for `boost` and for `libcurl`.
Then we can create a factory and use different implementation depends on config file settings.

- We can make possibility to control MollyBet websocket message precessing loop outside of MollyBet. 
E.g. instead `onMessageSync` function code we can pass callback function and call it instead of `onMessageSync` or 
inside `onMessageSync`.  
Good way for this can be using `atomic`, e.g. for analog of MollyBet::messageLoopStatus variable and in this 
way stop MollyBet websocket message precessing loop.  
In theory we can write something like: `ioc->stop()` for `net::io_context ioc` but this hard and e.g. we would have 
correct closing of open connections so this not very good way.
