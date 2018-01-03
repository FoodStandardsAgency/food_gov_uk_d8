Modules
=======

The following custom modules are used in this project.

## FSA Custom (`fsa_custom`)
FSA site small customizations and configuration pages. 

See [README.md](../drupal/web/modules/custom/fsa_custom/README.md) for more information.

## FSA ES (`fsa_es`)
Index FSA Ratings module-created entities to Elasticsearch for. 

See [README.md](../drupal/web/modules/custom/fsa_es/README.md) for more information.

## Linkit content (`linkit_content`)

Adds internal link/anchor search to WYSIWYG.

See [README.md](../drupal/web/modules/custom/linkit_content/README.md) for more information.

## FSA Notify (`fsa_notify`)
Send notifications to subscribed users using GOV.UK notify service. 

See [README.md](../drupal/web/modules/custom/fsa_notify/README.md) for more information.

## FSA Ratings (`fsa_ratings`)
Creates custom entities for FHRS Rating data.

## FSA Ratings Import (`fsa_ratings_import`)
Imports FHRS rating data from http://api.ratings.food.gov.uk/ to FSA custom entities using Migrate API.

## FSA team finder (`fsa_team_finder`)
Finds a food safety team for a given postcode.

This ajax form takes a postcode as input and, if valid, passes the query to a service. This service builds a request URL and sends it to MapIt (an external web service), for which the FSA has a paid account. If successful, MapIt will send back a JSON response, which will contain the name and identifier of the local authority responsible for the area of the given postcode. In the case of two-tier local government, details of more than one council will be returned. It is district council's which are responsible for food safety, hence it is these details that will be returned by the service.

Details of local authorities are stored in the database, so the relevant entity data can be queried, using the returned identifier from MapIt. The relevant email and site links for the council are generated, and then themed by a custom theme function. The form is rebuilt and the themed message displayed with the _found team_ information. The form may be reset and used as many times as necessary.

## FSA TOC (`fsa_toc`)

Small module to add anchors to h1-h6 tags of body fields.

See [README.md](../drupal/web/modules/custom/fsa_toc/README.md) for more information.
