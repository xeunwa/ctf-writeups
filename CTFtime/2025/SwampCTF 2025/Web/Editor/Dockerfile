# Stage 1: Build the Angular frontend
FROM node:latest AS build

WORKDIR /app

# Install dependencies for Angular
COPY frontend/package.json frontend/package-lock.json ./
RUN npm install

# Copy all the frontend files and build the Angular app
COPY frontend/ ./
RUN npm run build --output-path=dist/frontend --base-href=/

# Stage 2: Setup the Flask backend
FROM python:3.12

# Set working directory for Flask app
WORKDIR /app

# Install Python dependencies for Flask
COPY backend/requirements.txt /app/
RUN pip install -r requirements.txt

# Copy the Flask app and flag file into the container
COPY backend/server.py /app/
COPY backend/flag.txt /app/

# Copy the built Angular app from the first stage into Flask's static directory
COPY --from=build /app/dist/frontend /app/frontend

# Expose Flask's default port
EXPOSE 5000

# Run the Flask server
CMD ["python", "server.py"]

# Build: docker build -t editor .
# Run: docker run -p 5000:5000 editor
