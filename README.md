# transformer

> A proof-of-concept version of the **integrator.io** data transformer that takes in one JSON format and outputs a new one

With this little app, you can take one set of `POST` data and _transform_ the data to a new output format. The formatter uses [Twig](https://twig.symfony.com/) as the template engine for the reformatting and [Swoole](https://www.swoole.co.uk/) for the PHP server.

### To Do

- [x] Sandboxed templates
- [x] Template path
- [x] Multiple content types
- [x] `.env` support
- [ ] Remote templates

### Installation

* `cp .env.example .env`
* `composer install`
* `docker-compose up`

_This assumes you are using `docker.local` as your docker hostname..._

### Setup templates

* Open up one of the files under `templates/`
* Edit the template to massage how you want the output
* Start (or restart if running) once you save the template
* Make a request to the app passing the arguments for your `template` and `content-type`

```
http://docker.local:8080?template=json&content-type=application/json # identical to no params
http://docker.local:8080?template=html&content-type=text/html
http://docker.local:8080?template=csv&content-type=text/csv
```

### URL Callback

If a `callback` parameter is passed, the server will send the results of the request to that endpoint using the content type being passed through. This means the client making the initial request will get the response, _as well as_, the callback server.

You will notice that the server still responds quickly even though the server is making a request to another server. This is because Swoole has the ability to make the request _after_ the response (using deferred functionality) is sent back to our initializing client. This means there is no error handling for requests to the additional server since errors cannot be handled after the response.

```
http://docker.local:8080?template=csv&content-type=text/csv&callback=http://example.com/endpoint
```

This will transform the data and then also make a POST request to `http://example.com/endpoint` with a copy of the response.

### Usage

You can use the included test data to try out the endpoints. You can use curl in the following way:

```
curl -d "@tmp/example-post-data.json" -H "Content-Type: application/json" -X POST http://docker.local:8080
```

### Twig Sandbox

One of the benefits of using Twig for the template engine is that it comes with a [sandbox feature](https://twig.symfony.com/doc/2.x/api.html#sandbox-extension). This sandbox let's you control the filters and functions that can be used inside the twig templates.
