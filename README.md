### Disclaimer ###
This may seem to be overkill for a simple contact form to use such approach. But currently I work in a company where we have to develop a modular monolith
app, and it was easier for me to implement it this way.
## Key improvements that can be done ##
This is only a test app, so there are some shortcuts that can be improved.
1. The api is not protected. It would be better to protect it with a JWT token or some other authentication method. This was intentionally skipped.
2. The app store all data as simple text. It could use some encoding/decoding for all sensitive data like email and messages sent through the contact form. 
3. The app should have any authorization mechanism to prevent unauthorized users from accessing the data. Now the list endpoint is available publicly.
4. The app should have some behat and/or integration tests covering all the endpoints. Skipped due to not being requested at requirements.
5. There should be some kind of rate limiting for the endpoints.
6. Logging should be improved, it's only a simple log error, and no logging of the requests.
7. There should be two db connections, one read-only and one rw, preferably with a read replica.
8. There should be strings sanitization but requirements were not specified what should be allowed to send through the form so I allow only small subset of
   characters.
