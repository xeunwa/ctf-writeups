require 'json'
require 'sinatra/base'
require 'net/http'


# Base class for both Admin and Regular users
class Person
  @@url = "http://default-url.com"

  attr_accessor :name, :age, :details

  def initialize(name:, age:, details:)
    @name = name
    @age = age
    @details = details
  end

  def self.url
    @@url
  end

  # Method to merge additional data into the object
  def merge_with(additional)
    recursive_merge(self, additional)
  end

  private

  # Recursive merge to modify instance variables
  def recursive_merge(original, additional, current_obj = original)
    additional.each do |key, value|
      if value.is_a?(Hash)
        if current_obj.respond_to?(key)
          next_obj = current_obj.public_send(key)
          recursive_merge(original, value, next_obj)
        else
          new_object = Object.new
          current_obj.instance_variable_set("@#{key}", new_object)
          current_obj.singleton_class.attr_accessor key
        end
      else
        current_obj.instance_variable_set("@#{key}", value)
        current_obj.singleton_class.attr_accessor key
      end
    end
    original
  end
end

class User < Person
  def initialize(name:, age:, details:)
    super(name: name, age: age, details: details)
  end
end

# A class created to simulate signing with a key, to be infected with the third gadget
class KeySigner
  @@signing_key = "default-signing-key"

  def self.signing_key
    @@signing_key
  end

  def sign(signing_key, data)
    "#{data}-signed-with-#{signing_key}"
  end

  def test
    "User"
  end 

end

class JSONMergerApp < Sinatra::Base

  set :bind, '0.0.0.0'
  set :port, 4567
  get '/' do
    "Hello, Sinatra running in Docker!"
  end
  # POST /merge - Infects class variables using JSON input
  post '/merge' do
    content_type :json
    json_input = JSON.parse(request.body.read)

    user = User.new(
      name: "John Doe",
      age: 30,
      details: {
        "occupation" => "Engineer",
        "location" => {
          "city" => "Madrid",
          "country" => "Spain"
        }
      }
    )

    user.merge_with(json_input)

    { status: 'merged' }.to_json
  end

  # GET /launch-curl-command - Activates the first gadget
  get '/launch-curl-command' do
    content_type :json

    # This gadget makes an HTTP request to the URL stored in the User class
    if Person.respond_to?(:url)
      url = Person.url
      response = Net::HTTP.get_response(URI(url))
      { status: 'HTTP request made', url: url, response_body: response.body }.to_json
    else
      { status: 'Failed to access URL variable' }.to_json
    end
  end

  # Curl command to infect User class URL:
  # curl -X POST -H "Content-Type: application/json" -d '{"class":{"superclass":{"url":"http://example.com"}}}' http://localhost:4567/merge

  # GET /sign_with_subclass_key - Signs data using the signing key stored in KeySigner
  get '/sign_with_subclass_key' do
    content_type :json

    # This gadget signs data using the signing key stored in KeySigner class
    signer = KeySigner.new
    signed_data = signer.sign(KeySigner.signing_key, "data-to-sign")

    { status: 'Data signed', signing_key: KeySigner.signing_key, signed_data: signed_data }.to_json
  end

  # Curl command to infect KeySigner signing key (run in a loop until successful):
  # for i in {1..1000}; do curl -X POST -H "Content-Type: application/json" -d '{"class":{"superclass":{"superclass":{"subclasses":{"sample":{"signing_key":"injected-signing-key"}}}}}}' http://localhost:4567/merge; done

  # GET /check-infected-vars - Check if all variables have been infected
  get '/check-infected-vars' do
    content_type :json

    signer = KeySigner.new
    
    {
      user_url: Person.url,
      signing_key: KeySigner.signing_key,
      test: signer.test(),
      run: `ls`
    }.to_json
  end

  run! if app_file == $0
end
