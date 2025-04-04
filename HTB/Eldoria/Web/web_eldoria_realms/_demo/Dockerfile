# Use official Ruby image
FROM ruby:3.2

# Set working directory
WORKDIR /app

# Copy application files
COPY app.rb .
COPY Gemfile .

# Install dependencies
RUN gem install bundler
RUN bundle install

# Expose port 4567 for Sinatra
EXPOSE 4567

# Start the Sinatra server
CMD ["ruby", "app.rb"]
