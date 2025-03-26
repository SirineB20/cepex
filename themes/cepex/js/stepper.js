$(document).ready(function () {
    var currentStep, nextStep, previousStep;
    var opacity;
    var current = 0;
    var steps = $("fieldset").length;

    // Initialisation : Masquer toutes les étapes sauf la première
    $("fieldset").hide();
    $("fieldset").eq(current).show();
    setProgressBar(current);

    $(".next-step").click(function () {
        currentStep = $(this).closest("fieldset");
        nextStep = currentStep.next("fieldset");

        if (nextStep.length) {
            $("#progressbar li").eq($("fieldset").index(nextStep)).addClass("active");
            nextStep.show();
            currentStep.animate({ opacity: 0 }, {
                step: function (now) {
                    opacity = 1 - now;
                    currentStep.css({ 'display': 'none', 'position': 'relative' });
                    nextStep.css({ 'opacity': opacity });
                },
                duration: 500
            });
            setProgressBar(++current);
        }
    });

    $(".previous-step").click(function () {
        currentStep = $(this).closest("fieldset");
        previousStep = currentStep.prev("fieldset");

        if (previousStep.length) {
            $("#progressbar li").eq($("fieldset").index(currentStep)).removeClass("active");
            previousStep.show();
            currentStep.animate({ opacity: 0 }, {
                step: function (now) {
                    opacity = 1 - now;
                    currentStep.css({ 'display': 'none', 'position': 'relative' });
                    previousStep.css({ 'opacity': opacity });
                },
                duration: 500
            });
            setProgressBar(--current);
        }
    });

    function setProgressBar(currentStep) {
        var percent = parseFloat(100 / steps) * currentStep;
        percent = percent.toFixed();
        $(".progress-bar").css("width", percent + "%");
    }
});
