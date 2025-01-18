CXX := g++


# Betting variables
BETTING_CXXFLAGS := -c -Wall -std=c++23 -O2
# BETTING_CXXFLAGS := -c -Wall -std=c++23 -g


BETTING_PATH := betting
BETTING_SRC_PATH := $(BETTING_PATH)/src
BETTING_BUILD_PATH := $(BETTING_PATH)/build
BETTING_BIN_PATH := $(BETTING_PATH)/bin

#BETTING_SRC_INNER_DIRS := client curl mollybet
BETTING_SRC_INNER_DIRS := client mollybet
BETTING_OBJECT_DIRS := $(BETTING_SRC_INNER_DIRS:%=$(BETTING_BUILD_PATH)/%)
BETTING_SRC_DIRS := $(BETTING_SRC_PATH) $(BETTING_SRC_INNER_DIRS:%=$(BETTING_SRC_PATH)/%)
BETTING_SRC := $(wildcard $(addsuffix /*.cpp, $(BETTING_SRC_DIRS)))
BETTING_OBJECTS := $(patsubst $(BETTING_SRC_PATH)%.cpp,$(BETTING_BUILD_PATH)%.o,$(BETTING_SRC))

BETTING_EXECUTABLE := $(BETTING_BIN_PATH)/betting

BETTING_INCLUDES := -I$(BETTING_PATH)/include
BETTING_LIBS := -lcrypto -lssl


# Info variables
INFO_CXXFLAGS := -Wall -std=c++23 -O2

INFO_PATH := ./info
INFO_SRC_PATH := $(INFO_PATH)/src
INFO_BIN_PATH := $(INFO_PATH)/bin
INFO_EXECUTABLE_BOOST := $(INFO_BIN_PATH)/boost-info
REQUIRED_VERSION_BOOST := 108600
#INFO_EXECUTABLE_CURL := $(INFO_BIN_PATH)/curl-info
#REQUIRED_VERSION_LIBCURL := 8.11.0_wss_ws_https_http_SSL_HTTP/2
#INFO_EXECUTABLE := $(INFO_EXECUTABLE_BOOST) $(INFO_EXECUTABLE_CURL)
INFO_EXECUTABLE := $(INFO_EXECUTABLE_BOOST)

INFO_CHECK_COMMAND_FILTER := | grep INFO_HASH | awk '{print $$2}'


# Color variables
WARNING_COLOR := '\033[33m'
NO_COLOR := '\033[0m'


###############################################################################
# all
###############################################################################
all: check-info betting

clean: clean-info clean-betting


###############################################################################
# betting
###############################################################################
betting: $(BETTING_OBJECT_DIRS) $(BETTING_SOURCES) $(BETTING_EXECUTABLE)

$(BETTING_EXECUTABLE): $(BETTING_OBJECTS)
	$(CXX) $(BETTING_OBJECTS) -o $@ $(BETTING_LIBS)

$(BETTING_BUILD_PATH)/%.o: $(BETTING_SRC_PATH)/%.cpp
	$(CXX) $(BETTING_CXXFLAGS) $(BETTING_INCLUDES) $< -o $@

$(BETTING_OBJECT_DIRS):
	-mkdir $@

clean-betting:
	-rm -R $(BETTING_BUILD_PATH)/*
	-rm $(BETTING_BIN_PATH)/*


###############################################################################
# info
###############################################################################

# Need to check thet compiler use exatly needed version
check-info: bild-info
	$(eval CURRENT_VERSION_BOOST := "$(shell $(INFO_EXECUTABLE_BOOST) $(INFO_CHECK_COMMAND_FILTER))")
	@if [ $(REQUIRED_VERSION_BOOST) != $(CURRENT_VERSION_BOOST) ]; then \
		echo $(WARNING_COLOR)WARNING: Current boost C++ libraries version not the same as required.; \
	    echo Required version: $(REQUIRED_VERSION_BOOST), current version: $(CURRENT_VERSION_BOOST)$(NO_COLOR); \
	fi

# Build executable to take info hash
bild-info: $(INFO_EXECUTABLE)
$(INFO_EXECUTABLE):
	$(CXX) $(INFO_CXXFLAGS) $(INFO_SRC_PATH)/$(@F).cpp -o $@ -lcurl

#	$(eval CURRENT_VERSION_LIBCURL := "$(shell $(INFO_EXECUTABLE_CURL) $(INFO_CHECK_COMMAND_FILTER))")
#	@if [ $(REQUIRED_VERSION_LIBCURL) != $(CURRENT_VERSION_LIBCURL) ]; then \
#	    echo $(WARNING_COLOR)WARNING: Current libcurl C++ library version not the same as required.; \
#	    echo Required version: $(REQUIRED_VERSION_LIBCURL), current version: $(CURRENT_VERSION_LIBCURL)$(NO_COLOR); \
#	fi

clean-info:
	-rm $(INFO_BIN_PATH)/*
