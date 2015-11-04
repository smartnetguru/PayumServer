if("undefined"==typeof jQuery) {
    throw new Error("Payum's JavaScript requires jQuery");
}

Payum = {
    render: function(url, container) {
        var payum = this;

        jQuery.ajax(url, {
            type: "GET",
            async: true,
            headers: {
                Accept: 'application/vnd.payum+json'
            },
            success: function(data) {
                payum.updateContainer(data, container);

                $(container + ' form').on('submit', function (e) {
                    e.preventDefault();

                    var form = $(this);

                    var values = {};
                    $.each(form.serializeArray(), function (i, field) {
                        values[field.name] = field.value;
                    });

                    jQuery.ajax(form.attr('action'), {
                        type: "POST",
                        headers: {
                            Accept: 'application/vnd.payum+json'
                        },
                        data: values,
                        success: function(data) {
                            payum.updateContainer(data, container);
                        },
                        complete: function() {
                            //console.log('complete', arguments);
                        },
                        error: function() {
                            //console.log('complete', arguments);
                        }
                    });
                });
            },
            complete: function() {
                //console.log('complete', arguments);
            },
            error: function() {
                //console.log('complete', arguments);
            }
        });
    },

    updateContainer: function(data, container)
    {
        if (data.status == 302) {
            window.location.replace(data.headers.Location);
        }
        if (data.status >= 200 && data.status < 300) {
            $(container).html(data.content);
        }
    }
};