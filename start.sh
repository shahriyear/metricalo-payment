#!/bin/bash

# Script for setting up Docker environment and initializing application

echo "Starting the setup process..."

# Step 0: Check if Docker is installed
if ! [ -x "$(command -v docker)" ]; then
    echo "Error: Docker is not installed or not in PATH. Please install Docker and try again."
    exit 1
fi

# Step 0.1: Check if Docker Compose is installed
if ! [ -x "$(command -v docker compose)" ]; then
    echo "Error: Docker Compose is not installed or not in PATH. Please install Docker Compose and try again."
    exit 1
fi

# Step 1: Build Docker images
echo "Building Docker images..."
docker compose build
if [ $? -ne 0 ]; then
    echo "Error during Docker build. Exiting."
    exit 1
fi

# Step 2: Start Docker containers in detached mode
echo "Starting Docker containers..."
docker compose up -d
if [ $? -ne 0 ]; then
    echo "Error starting Docker containers. Exiting."
    exit 1
fi

# Step 3: Run Composer install in payment-php container
echo "Running 'composer install' inside the payment-php container..."
docker exec -it payment-php composer install
if [ $? -ne 0 ]; then
    echo "Error running 'composer install'. Exiting."
    exit 1
fi

# Step 4: Docker containers
echo "Docker containers are running..."
docker compose ps
if [ $? -ne 0 ]; then
    echo "Error during Docker container check. Exiting."
    exit 1
fi

# Step 5: Run tests using Composer
echo "Running tests in payment-php container..."
docker exec -it payment-php composer tests
if [ $? -ne 0 ]; then
    echo "Tests failed. Check the logs for details."
    exit 1
fi

echo "Setup process completed successfully!"
