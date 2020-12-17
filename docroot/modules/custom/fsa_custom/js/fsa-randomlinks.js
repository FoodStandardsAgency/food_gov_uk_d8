function randomlinks(){
    var myrandom=Math.round(Math.random()*4)
    var links=new Array()
    links[0]="https://forms.office.com/Pages/ResponsePage.aspx?id=-VAcircBikym-pDrkG8Y6XugaYrj8fNHhyCFRWGmjRJURVNPWlNZRjU0NUpHWlpTN1BCUkw4VVo0WiQlQCN0PWcu";
    links[1]="https://forms.office.com/Pages/ResponsePage.aspx?id=-VAcircBikym-pDrkG8Y6XugaYrj8fNHhyCFRWGmjRJUN0ZZU1FQSjdESlkxSk1JVzhNRlVVRzdMSiQlQCN0PWcu";
    links[2]="https://forms.office.com/Pages/ResponsePage.aspx?id=-VAcircBikym-pDrkG8Y6XugaYrj8fNHhyCFRWGmjRJUREdCRVJDOFVZVUxPOFJWUFZNNzBGQ0hERSQlQCN0PWcu";
    links[3]="https://forms.office.com/Pages/ResponsePage.aspx?id=-VAcircBikym-pDrkG8Y6XugaYrj8fNHhyCFRWGmjRJUNFQ2UFc4T0JXWTJaM0RBVEdOODlQR0cwWiQlQCN0PWcu";
    links[4]="https://forms.office.com/Pages/ResponsePage.aspx?id=-VAcircBikym-pDrkG8Y6XugaYrj8fNHhyCFRWGmjRJUMkIwRDYzQTRHTVJZMkhaNTFCSE5KNEpLNyQlQCN0PWcu";
    return links[myrandom];
}

(function ($) {
  'use strict';
  Drupal.behaviors.fsaRandomLinks = {
    attach: function (context, settings) {
      $("a.randomLink").each(function () {
        $(this).click(function() { window.open(randomLinks()); });
      });
    }
  };
}(jQuery));
