Feature: Imbo requires an access token for read operations
    In order to get content from Imbo
    As an HTTP Client
    I must specify an access token in the URI

    Background:
        Given "tests/phpunit/Fixtures/image.png" exists in Imbo
        And Imbo uses the "access-token.php" configuration

    Scenario: Request user information using the correct private key
        Given I use "publickey" and "privatekey" for public and private keys
        And I include an access token in the query
        When I request "/users/publickey"
        Then I should get a response with "200 OK"

    Scenario: Request user information using the wrong private key
        Given I use "publickey" and "foobar" for public and private keys
        And I include an access token in the query
        When I request "/users/publickey"
        Then I should get a response with "400 Incorrect access token"
        And the Imbo error message is "Incorrect access token" and the error code is "0"

    Scenario: Request user information without a valid access token
        Given I use "publickey" and "foobar" for public and private keys
        When I request "/users/publickey"
        Then I should get a response with "400 Missing access token"
        And the Imbo error message is "Missing access token" and the error code is "0"

    Scenario: Request image using no access token
        Given I use "publickey" and "privatekey" for public and private keys
        And the "Accept" request header is "*/*"
        When I request "/users/publickey/images/929db9c5fc3099f7576f5655207eba47"
        Then I should get a response with "400 Missing access token"

    Scenario: Can request a whitelisted transformation without access tokens
        Given I use "publickey" and "privatekey" for public and private keys
        And the "Accept" request header is "*/*"
        When I request "/users/publickey/images/929db9c5fc3099f7576f5655207eba47?t[]=whitelisted"
        Then I should get a response with "200 OK"
        And the width of the image is "100"
        And the height of the image is "50"

    Scenario: Can not issue transformations that are not whitelisted without a valid access token
        Given I use "publickey" and "privatekey" for public and private keys
        And the "Accept" request header is "*/*"
        When I request "/users/publickey/images/929db9c5fc3099f7576f5655207eba47?t[]=thumbnail"
        Then I should get a response with "400 Missing access token"
