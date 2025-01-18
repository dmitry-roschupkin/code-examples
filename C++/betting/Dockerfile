FROM gcc:14.2.0-bookworm

RUN apt-get update -y
RUN apt-get upgrade -y

RUN mkdir /opt/lib

###############################################################################
# Install openssl
###############################################################################
# WORKDIR /opt/lib
# RUN wget https://github.com/openssl/openssl/releases/download/openssl-3.4.0/openssl-3.4.0.tar.gz
# RUN tar -xvzf openssl-3.4.0.tar.gz
# RUN rm openssl-3.4.0.tar.gz
# WORKDIR /opt/lib/openssl-3.4.0
# RUN ./config
# RUN make
# RUN make install
# RUN ldconfig

###############################################################################
# Install boost 1.86
###############################################################################
WORKDIR /opt/lib
RUN wget https://archives.boost.io/release/1.86.0/source/boost_1_86_0.tar.gz
RUN tar -xvzf boost_1_86_0.tar.gz
RUN rm boost_1_86_0.tar.gz
WORKDIR /opt/lib/boost_1_86_0
RUN ./bootstrap.sh
RUN ./b2
RUN ./b2 install
RUN ldconfig

###############################################################################
# Install curl and curllib 8.11 (needed only for spikes)
###############################################################################
WORKDIR /opt/lib
RUN apt purge -y libcurl4-openssl-dev
RUN apt-get install -y libpsl-dev libnghttp2-dev
RUN wget https://curl.se/download/curl-8.11.0.tar.gz
RUN tar -xvzf curl-8.11.0.tar.gz
RUN rm curl-8.11.0.tar.gz
WORKDIR /opt/lib/curl-8.11.0
RUN ./configure --with-openssl --with-nghttp2
RUN make
RUN make install
RUN ldconfig

###############################################################################
# Install libraries form packages (next libs are needed only for spikes)
###############################################################################
RUN apt-get install nlohmann-json3-dev
RUN apt-get install libwebsocketpp-dev
RUN ldconfig

RUN apt-get install -y gawk
# RUN apt-get install -y mc aptitude
###############################################################################

COPY . /opt/betting/
WORKDIR /opt/betting

RUN make clean
RUN make

CMD ./betting/bin/betting
