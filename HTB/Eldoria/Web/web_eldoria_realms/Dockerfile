FROM ruby:3

# Copy flag
COPY flag.txt /flag.txt

# Install packages
RUN apt-get update && apt-get install -y netcat-traditional supervisor protobuf-compiler && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install curl with shared library support
RUN wget https://curl.haxx.se/download/curl-7.70.0.tar.gz && \
    tar xfz curl-7.70.0.tar.gz && \
    cd curl-7.70.0/ && \
    ./configure --with-ssl --enable-shared && \
    make -j16 && \
    make install && \
    ldconfig

# Install Golang 1.21
RUN wget https://go.dev/dl/go1.21.0.linux-amd64.tar.gz \
    && tar -C /usr/local -xzf go1.21.0.linux-amd64.tar.gz \
    && rm go1.21.0.linux-amd64.tar.gz

# Update PATH to include Go binaries and go install binaries
ENV PATH="/usr/local/go/bin:/root/go/bin:${PATH}"

# Set working directory
WORKDIR /app

# Copy files
COPY challenge ./
RUN cp /app/live_data.proto /app/data_stream_api
RUN mv /app/live_data.proto /app/eldoria_api

# CD to eldoria api
WORKDIR /app/eldoria_api

# Install bundler and grpc-tools
RUN gem install bundler grpc-tools

# Install required gems (Sinatra, etc.)
RUN bundle install

# Compile proto files
RUN grpc_tools_ruby_protoc -I . --ruby_out=. --grpc_out=. live_data.proto
RUN sed -i "s/require 'live_data_pb'/require_relative 'live_data_pb'/g" live_data_services_pb.rb

# CD to live data api
WORKDIR /app/data_stream_api

# Install the required protobuf plugins
RUN go install google.golang.org/protobuf/cmd/protoc-gen-go@latest && \
    go install google.golang.org/grpc/cmd/protoc-gen-go-grpc@latest

# Create a separate directory for the generated code and generate it with a valid import path mapping.
RUN mkdir -p pb && \
    protoc --go_out=paths=source_relative,Mlive_data.proto=app/pb:./pb \
           --go-grpc_out=paths=source_relative,Mlive_data.proto=app/pb:./pb \
           live_data.proto

# Compile api
RUN go mod init app
RUN go mod tidy
RUN go mod download google.golang.org/grpc google.golang.org/protobuf
RUN go build -o data_stream_api .

# Setup supervisor
COPY conf/supervisord.conf /etc/supervisord.conf

# Expose the default Sinatra port
EXPOSE 1337

# Start supervisord
COPY --chown=root entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh
ENTRYPOINT ["/entrypoint.sh"]
