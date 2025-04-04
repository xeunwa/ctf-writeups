require 'sinatra'
require 'rack/proxy'
require 'json'

class ReverseProxy < Rack::Proxy
  def perform_request(env)
    request = Rack::Request.new(env)

    # Only allow requests to the /api?action=getInfo endpoint
    if request.params['action'] == 'getInfo'
      env['HTTP_HOST'] = 'backend:5000'
      env['PATH_INFO'] = '/api'
      env['QUERY_STRING'] = request.query_string
      body = request.body.read
      env['rack.input'] = StringIO.new(body)
      
      begin
        json_data = JSON.parse(body)
        puts "Received valid JSON data: #{json_data}"
        super(env)
      rescue JSON::ParserError => e
        puts "Error parsing JSON: #{e.message}"
        return [200, { 'Content-Type' => 'application/json' }, [{ message: "Error parsing JSON", error: e.message }.to_json]]
      end
    else
      [200, { 'Content-Type' => 'text/plain' }, ["Unauthorized"]]
    end
  end
end

use ReverseProxy

set :bind, '0.0.0.0'
set :port, 8080
puts "Server is listening on port 8080..."