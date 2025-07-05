# Metro Markets Coding Challenge

## Prerequisites

You need to have [Docker](https://www.docker.com/) and [docker-compose](https://docs.docker.com/compose/) installed.

## Running the Project

For convenience I've included a Makefile.

Run `make help` for information on all the commands.

### Setup

First, set up the project

```bash
make setup
```

### Fetch Prices

```bash
make fetch
```

### Price Endpoints

All API endpoints are protected. You'll require a valid API key to be sent in the `X-API-KEY` header per request.

#### Authentication

Generate an API key. Run the following command, replacing `<YOUR_EMAIL>` with an email address:

```bash
make create-user EMAIL=<YOUR_EMAIL>
```

This will output a new API key. Copy this key for use in every request.

#### Get All Prices

Retrieve a list of all lowest prices by running the following command, replacing `<YOUR_API_KEY>` with the key you copied in the previous steps.

```bash
curl 'http://localhost:8000/api/prices/' --header 'X-API-KEY: <YOUR_API_KEY>'
```

#### Get Price By ID

Retrieve a the lowest price of a specific product by running the following command, replacing `<YOUR_API_KEY>` with the key you copied in the previous steps and `<PRODUCT_ID>` with the desired Product ID.

```bash
curl 'http://localhost:8000/api/prices/<PRODUCT_ID>' --header 'X-API-KEY: <YOUR_API_KEY>'
```

### Tests

```bash
make test
```

## Approach

### Business Scope

The most important part of the scope is the business side of the project. For the implementation I'm choosing to prioritize data integrity and simplicity over resilience and efficiency. I will do all price fetching in batches, and will process everything asynchronously per batch of fetched prices. That will increase the data integrity by always having complete data every time. However, that compromises resilience; if one individual API fails consistently, even when retrying, the price for that period of time will not be updated.

The way that I would deal with this would be to have robust monitoring and alerting in place to know when a specific part of price fetching is failing, to remove it temporarily or fix it. However, this is out of the scope of the task, but worth mentioning.

### Technical Details

#### Overview

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

#### Architecture

I'll have a container with Symfony, one for MySQL, and a few for the mock APIs.

For the mock APIs I will use [json-server](https://github.com/typicode/json-server). With the help of AI, I have generated some different datasets to work with to populate the mock database/API to fetch from.

To handle the processing of the data I will use the Strategy pattern, to have a common interface to parse the API data and extract prices with each API's specific details. Then to keep things tidy, the parsers will pass around DTOs to save to the DB.

To handle retry logic I will do fetching asynchronously by usings queues, leveraging Symfony Messenger. I'll implement a basic retry strategy and accumulate failed messages in a dead letter queue.

For testing, I will write mostly unit tests testing the parsing logic, and maybe one integration test that makes sure that the services are set up and working correctly.

For the API, I'll use standard REST API principles. For security I'll define a custom Authenticator to validate requests with the `X-API-KEY` header.

#### Code Structure

```
.
├── Dockerfile
├── Makefile
├── app/
│   ├── bin/
│   ├── config/
│   ├── src/
│   └── ...
├── docker-compose.yml
└── mock-api-data/
    ├── db-one.json
    ├── db-two.json
    └── db-three.json
```

The Symfony application lives in the `app` directory.

- `src/Command` contains the main entry point command for fetching prices
- `src/Dto` contains the definition of DTOs to handle normalized data across the app
- `src/Message` and `src/MessageHandler` contains Symfony Messenger related logic
- `src/PriceFetcher` contains all logic for retrieving price data from APIs
  - `src/PriceFetcher/Strategy` contains specific logic per API to fetch and parse data
- `src/PriceFinder` contains the logic for finding the lowest price from the aggregated price information from APIs
- `src/PriceSaver` contains the logic responsible of persisting data into the database
- `src/Entity` contains the Doctrine entities
- `src/Controller` contains the logic of the pricing routes
- `src/EventListener` contains a listener to process uncaught exceptions
- `src/Security` contains the logic for authenticating API requests
- `tests/` contains all unit and integration tests

#### Considerations

##### Error Handling

For the scope of the task, I chose not to have robust error handling, just some basic handling for the main functionality.

##### Batch Processing

Even though I'm choosing to do the price fetching in batches that will fail if one of them fails, with the overall architecture, this can still be easily adapted to process only successful fetches and get the prices.

By fetching in batches I'm also trading off efficiency. During retries the fetching will have to redo all API calls, even if just one fails. Also, the slowest API response will bottleneck the whole processing. However, we can improve this by adding a cache layer for calls that are successful, so we'd only do calls for failing fetches.

In a real world application, I'd seek clarification from business to decide on what would work best.

##### Asynchronous Processing

By separating the fetching and parsing, finding the lowest price, and saving data in different, independent, and asynchronous steps allows to further extend each of them as needed. 

For example, we could have specific ways to authenticate the requests to the different APIs inidividually. We could further aggregate the raw prices when finding the lowest price, or having different strategies for specifc vendors, etc. And of course, we can do more than just saving the data in the database in the last step, like refresh a cache, send messages, etc.
