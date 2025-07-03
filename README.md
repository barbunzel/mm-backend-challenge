# Metro Markets Coding Challenge

## Approach

### Overview

I will tackle this as part of the main functionality:

- All functional requirements
- Containerization with Docker
- Retry logic for failed fetches
- Basic test suite

If I get that done within a reasonable time, I will attempt:

- Scheduling
- Cache layer

My plan is to build a small Symofny app, with the latest Symfony and PHP versions,
using Docker.

I will set everything up with Docker from the beginning, to make it easier to work with and review.

I will first focus on the price fetching part of the assignment, and aftewards on the API part.

### Architecture

I'll have a container with Symfony, one for MySQL, and a few for the mock APIs.

For the mock APIs I will use [json-server](https://github.com/typicode/json-server). With the help of AI, I have generated some different datasets to work with to populate the mock database/API to fetch from.

To handle the processing of the data I will use the Strategy pattern, to have a common interface to parse the API data and extract prices with each API's specific details. Then to keep things tidy, the parsers will pass around DTOs to save to the DB.

To handle retry logic I will do fetching asynchronously by usings queues, leveraging Symfony Messenger. I'll implement a basic retry strategy and accumulate failed messages in a dead letter queue.

For testing, I will write mostly unit tests testing the parsing logic, and maybe one integration test that makes sure that the services are set up and working correctly.

For the API, I'll use standard REST API principles. For security I'll define a custom Authenticator to validate requests with the `X-API-KEY` header.
