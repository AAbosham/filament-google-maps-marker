const mix = require("laravel-mix");

mix.disableNotifications();

mix.setPublicPath("./resources/dist")
    .minify('./resources/js/filament-google-maps-marker-core.js', './resources/dist/filament-google-maps-marker-core.min.js')
    .postCss("./resources/css/filament-google-maps-marker.css", "filament-google-maps-marker.css", [
        require("tailwindcss")("./tailwind.config.js"),
    ])
    .options({
        processCssUrls: false,
    });
