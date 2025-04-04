require "json"
require "sinatra/base"
require "net/http"
require "grpc"
require "open3"
require_relative "live_data_services_pb"

$quests = [
	{ "quest_id" => 1, "title" => "Defeat the Goblin Horde", "description" => "Eliminate the goblin invaders in the Shadow Woods.", "reward" => "50 gold", "status" => "available" },
	{ "quest_id" => 2, "title" => "Rescue the Captured Villagers", "description" => "Save villagers from the dark creatures in the Twilight Fields.", "reward" => "100 gold", "status" => "available" },
	{ "quest_id" => 3, "title" => "Retrieve the Lost Artifact", "description" => "Find the ancient artifact hidden in the Crystal Caverns.", "reward" => "Mystic weapon", "status" => "available" }
]

$store_items = [
	{ "item_id" => 1, "name" => "Health Potion", "price" => 10 },
	{ "item_id" => 2, "name" => "Mana Potion", "price" => 12 },
	{ "item_id" => 3, "name" => "Iron Sword", "price" => 50 },
	{ "item_id" => 4, "name" => "Leather Armor", "price" => 40 }
]

$player = nil

class Adventurer
	@@realm_url = "http://eldoria-realm.htb"

	attr_accessor :name, :age, :attributes

	def self.realm_url
		@@realm_url
	end

	def initialize(name:, age:, attributes:)
		@name = name
		@age = age
		@attributes = attributes
	end

	def merge_with(additional)
		recursive_merge(self, additional)
	end

	private

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

class Player < Adventurer
	def initialize(name:, age:, attributes:)
		super(name: name, age: age, attributes: attributes)
	end
end

class LiveDataClient
	def initialize(host = "localhost:50051")
		@stub = Live::LiveDataService::Stub.new(host, :this_channel_is_insecure)
	end

	def stream_live_data
		req = Live::LiveDataRequest.new
		@stub.stream_live_data(req).each do |live_data|
			yield live_data
		end
	rescue GRPC::BadStatus => e
		puts "gRPC Error: #{e.message}"
	end
end

class EldoriaAPI < Sinatra::Base
	set :port, 1337
	set :bind, "0.0.0.0"
	set :public_folder, File.join(File.dirname(__FILE__), "public")

	get "/" do
		send_file File.join(settings.public_folder, "index.html")
	end

	post "/merge-fates" do
		content_type :json
		json_input = JSON.parse(request.body.read)
		random_attributes = {
			"class" => ["Warrior", "Mage", "Rogue", "Cleric"].sample,
			"guild" => ["The Unbound", "Order of the Phoenix", "The Fallen", "Guardians of the Realm"].sample,
			"location" => {
				"realm" => "Eldoria",
				"zone" => ["Twilight Fields", "Shadow Woods", "Crystal Caverns", "Flaming Peaks"].sample
			},
			"inventory" => []
		}

		$player = Player.new(
			name: "Valiant Hero",
			age: 21,
			attributes: random_attributes
		)

		$player.merge_with(json_input)
		{ 
			status: "Fates merged", 
			player: { 
				name: $player.name, 
				age: $player.age, 
				attributes: $player.attributes 
			} 
		}.to_json
	end

	get "/invoke-helios" do
		content_type :json
		{
			message: "Brave new heroes, I am Helios. Your cries for adventure have summoned me here, ready to answer that call. Form a fellowship with adventurers far and wide, so that you may reclaim power from the dark ruler 'Malakar'. Your actions will echo through both this realm and your own. Unite and conquer, then you may return.",
			status: "summoned"
		}.to_json
	end

	get "/connect-realm" do
		content_type :json
		if Adventurer.respond_to?(:realm_url)
			realm_url = Adventurer.realm_url
			begin
				uri = URI.parse(realm_url)
				stdout, stderr, status = Open3.capture3("curl", "-o", "/dev/null", "-w", "%{http_code}", uri)
				{ status: "HTTP request made", realm_url: realm_url, response_body: stdout }.to_json
			rescue URI::InvalidURIError => e
				{ status: "Invalid URL: #{e.message}", realm_url: realm_url }.to_json
			end
		else
			{ status: "Failed to access realm URL" }.to_json
		end
	end

	get "/player-status" do
		content_type :json
		if $player.nil?
			random_attributes = {
				"class" => ["Warrior", "Mage", "Rogue", "Cleric"].sample,
				"guild" => ["The Unbound", "Order of the Phoenix", "The Fallen", "Guardians of the Realm"].sample,
				"location" => {
					"realm" => "Eldoria",
					"zone" => ["Twilight Fields", "Shadow Woods", "Crystal Caverns", "Flaming Peaks"].sample
				},
				"inventory" => []
			}
			$player = Player.new(name: "Valiant Hero", age: 21, attributes: random_attributes)
		end
		{ 
			status: "Player status", 
			player: { 
				name: $player.name, 
				age: $player.age, 
				attributes: $player.attributes 
			} 
		}.to_json
	end

	get "/quest-log" do
		content_type :json
		{ status: "Quest log", quests: $quests }.to_json
	end

	post "/complete-quest" do
		content_type :json
		data = JSON.parse(request.body.read)
		quest_id = data["quest_id"]
		quest = $quests.find { |q| q["quest_id"] == quest_id }
		if quest.nil?
			{ status: "Quest not found" }.to_json
		else
			if quest["status"] == "completed"
				{ status: "Quest already completed" }.to_json
			else
				quest["status"] = "completed"
				{ status: "Quest completed", quest: quest }.to_json
			end
		end
	end

	get "/store" do
		content_type :json
		{ status: "Store items", items: $store_items }.to_json
	end

	post "/equip-item" do
		content_type :json
		data = JSON.parse(request.body.read)
		item_id = data["item_id"]
		item = $store_items.find { |i| i["item_id"] == item_id }
		if item.nil?
			{ status: "Item not found in store" }.to_json
		else
			if $player.nil?
				random_attributes = {
					"class" => ["Warrior", "Mage", "Rogue", "Cleric"].sample,
					"guild" => ["The Unbound", "Order of the Phoenix", "The Fallen", "Guardians of the Realm"].sample,
					"location" => {
						"realm" => "Eldoria",
						"zone" => ["Twilight Fields", "Shadow Woods", "Crystal Caverns", "Flaming Peaks"].sample
					},
					"inventory" => []
				}
				$player = Player.new(name: "Valiant Hero", age: 21, attributes: random_attributes)
			end
			$player.attributes["inventory"] << item
			{ status: "Item equipped", inventory: $player.attributes["inventory"] }.to_json
		end
	end

	get "/fellowship" do
		content_type :json
		party = [
			{ "name" => "Aria", "class" => "Mage", "role" => "Spellcaster" },
			{ "name" => "Borin", "class" => "Warrior", "role" => "Tank" },
			{ "name" => "Liora", "class" => "Rogue", "role" => "Scout" }
		]
		{ status: "Fellowship retrieved", party: party }.to_json
	end

	get "/live-data" do
		content_type :json
		client = LiveDataClient.new
		live_messages = []
		count = 0
		client.stream_live_data do |live_data|
			live_messages << { 
				timestamp: live_data.timestamp, 
				message: live_data.message, 
				type: live_data.type 
			}
			count += 1
			break if count >= 5
		end
		{ status: "Live data fetched", data: live_messages }.to_json
	end
	# add this 
	get '/check-infected-vars' do
		content_type :json
	
		{
		  user_url: Adventurer.realm_url,
		  livedataclient: LiveDataClient.methods,
		  livedataclient_classvars: LiveDataClient.class_variables.map { |var| [var, LiveDataClient.class_variable_get(var)] }.to_h,
		}.to_json
	end

	run! if app_file == $0
end
