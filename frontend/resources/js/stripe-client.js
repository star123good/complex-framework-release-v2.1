
// stripe client class
const StripeClient = function() {
    this.stripe = Stripe(StripeClient.PUBLISHABLE_KEY);
    this.card = null;
    this.clientSecret = null;
};

// stripe publishable key
StripeClient.PUBLISHABLE_KEY = (typeof STRIPE_PUBLISHABLE_KEY === "string")?STRIPE_PUBLISHABLE_KEY:"";

// stripe card setup nodes
StripeClient.ELEMENT_CARD_SETUP = "#card-form";

// stripe card nodes
StripeClient.ELEMENT_CARD = "#card-element";


// stripe create card setup element
StripeClient.prototype.createCardSetup = function() {
    let $card = $(StripeClient.ELEMENT_CARD);
    if ($card.length) {
        // Create our card inputs
        var style = {
            base: { 
                color: "#32325d", 
                fontSize: '16px',
                "::placeholder": { color: "rgba(0,0,0,0.4)" }
            }
        };

        var elements = this.stripe.elements();
        var cardElement = elements.create('card', { style });
        cardElement.mount(StripeClient.ELEMENT_CARD);

        this.card = cardElement;
    }
};

// get client secret from API
StripeClient.prototype.getClientSecretFromAPI = function(next) {
    // check client secret is not null
    let that = this;
    if (that.clientSecret) {
        // get client secret
        next(that.clientSecret);
    }
    else {
        // get from API
        api.send({
            path : '/api/payments/create-setup-intent',
            success : function(data) {
                // set client secret
                if (data.client_secret) {
                    that.clientSecret = data.client_secret;
                    next(that.clientSecret);
                }
            },
            error : function(err) {
                console.log(err);
                next(null);
            }
        });
    }
};

// save stripe customer informations to API
StripeClient.prototype.saveCustomerToAPI = function(data, next) {
    // post to API
    api.send({
        data : data,
        path : '/api/payments/save-payment-method',
        success : function(data) {
            next(null);
        },
        error : function(err) {
            next(err);
        }
    });
};

// stripe confirm card setup
StripeClient.prototype.confirmCardSetup = function() {
    let $form = $(StripeClient.ELEMENT_CARD_SETUP);
    let that = this, cardholderName;

    if ($form.length) {
        // client secret
        that.getClientSecretFromAPI(function(clientSecret) {
            if (clientSecret) {
                // card holder name
                cardholderName = $form.find('input[name="nameOnCard"]').val();

                that.stripe.confirmCardSetup(
                    clientSecret,
                    {
                        payment_method: {
                            card: that.card,
                            billing_details: {
                                name: cardholderName,
                            },
                        },
                    }
                ).then(function(result) {
                    if (result.error) {
                        // Display error.message in your UI.
                        showAlarmDiv($form, result.error.message);
                    } else {
                        // The setup has succeeded. Display a success message.
                        if (result.setupIntent.status == "succeeded") {
                            stripe.saveCustomerToAPI({
                                payment_method : result.setupIntent.payment_method,
                            }, function(err) {
                                if (err) {
                                    showAlarmDiv($form, err);
                                }
                                else {
                                    // close modal
                                    $(StripeClient.ELEMENT_CARD_SETUP)
                                        .closest('div.mfp-content').find('button.mfp-close').click();
                                    window.location.reload();
                                }
                            });
                        }
                    }
                });
            }
        });
    }
};

// stripe connect account
StripeClient.prototype.connectAccount = function() {
    // get to API for oauth link
    api.send({
        type : 'GET',
        path : '/api/payments/get-oauth-link',
        success : function(data) {

            // redirect
            window.location = data.url;

        }
    });
};


// stripe client
var stripe = new StripeClient();


$(document).ready(function() {

    // card setup submit clicked
    $(document).on('click', StripeClient.ELEMENT_CARD_SETUP+' button[type="submit"]', function(event) {
        event.preventDefault();
        stripe.confirmCardSetup();
    });

    // create stripe account clicked
    $(document).on('click', '#button-create-stripe-account', function(event) {
        stripe.connectAccount();
    });

    // create some elements
    stripe.createCardSetup();

});