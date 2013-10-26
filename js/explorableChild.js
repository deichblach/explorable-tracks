(function($) {
    $(window).resize(function() {
        resizeMap();
    });
    $(window).ready(function() {
        resizeMap();
        initHistory();
        ajaxifyLinks();
    });
    $(window).load(function() {
        $('#et-slider-wrapper').mCustomScrollbar({
            scrollInertia: 0,
            autoHideScrollbar: true,
            advanced: {
                updateOnContentResize: Boolean
            },
            callbacks: {
                onScroll: function() {
                    updateScrollShadows();
                }
            }
        });
        resetShadows();
    });


})(jQuery);
function resetShadows() {
    (function($) {
        var sliderWrapper = document.querySelector('.mCSB_container');
        $('#et-slider-shadow-top').hide();
        if (sliderWrapper.offsetHeight < sliderWrapper.scrollHeight) {
            $('#et-slider-shadow-bottom').show();
        } else {
            $('#et-slider-shadow-bottom').hide();
        }
    })(jQuery);
}
function updateScrollShadows() {
    (function($) {
        if (mcs.top < 0) {
            $('#et-slider-shadow-top').show();
        } else {
            $('#et-slider-shadow-top').hide();
        }
        if (mcs.topPct < 100) {
            $('#et-slider-shadow-bottom').show();
        } else {
            $('#et-slider-shadow-bottom').hide();
        }
    })(jQuery);
}
function ajaxifyLinks() {
    (function($) {
        //Retrieve all links of class 'ajax'
        $('.ajax').click(function() {
            postId = getQueryVariable($(this).attr('href'), 't');
            if (postId) {
                loadPost(postId);
                return false;
            }
        });
    })(jQuery);
}
function initHistory() {
    (function($) {

        // Prepare
        var History = window.History; // Note: We are using a capital H instead of a lower h
        if (!History.enabled) {
            // History.js is disabled for this browser.
            // This is because we can optionally choose to support HTML4 browsers or not.
            return false;
        }

        // Bind to StateChange Event
        History.Adapter.bind(window, 'statechange', function() { // Note: We are using statechange instead of popstate
            var State = History.getState();
            doLoadPost(State.data.postid);
        });
    })(jQuery);
}
function getQueryVariable(url, variable) {
    var query = url.split('?');
    if (query.length > 1) {
        var vars = query[1].split('&');
        for (var i = 0; i < vars.length; i++) {
            var pair = vars[i].split('=');
            if (decodeURIComponent(pair[0]) == variable) {
                return decodeURIComponent(pair[1]);
            }
        }
    }

    console.log('Query variable %s not found in %s', variable, url);
}
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
function getBoundsZoomLevel(bounds, mapDim) {
    var WORLD_DIM = {height: 256, width: 256};
    var ZOOM_MAX = 21;

    function latRad(lat) {
        var sin = Math.sin(lat * Math.PI / 180);
        var radX2 = Math.log((1 + sin) / (1 - sin)) / 2;
        return Math.max(Math.min(radX2, Math.PI), -Math.PI) / 2;
    }

    function zoom(mapPx, worldPx, fraction) {
        return Math.floor(Math.log(mapPx / worldPx / fraction) / Math.LN2);
    }

    var ne = bounds.getNorthEast();
    var sw = bounds.getSouthWest();

    var latFraction = (latRad(ne.lat()) - latRad(sw.lat())) / Math.PI;

    var lngDiff = ne.lng() - sw.lng();
    var lngFraction = ((lngDiff < 0) ? (lngDiff + 360) : lngDiff) / 360;

    var latZoom = zoom(mapDim.height, WORLD_DIM.height, latFraction);
    var lngZoom = zoom(mapDim.width, WORLD_DIM.width, lngFraction);

    return Math.min(latZoom, lngZoom, ZOOM_MAX);
}
function loadPost(postId) {
    var loadUrl = "./?t=" + postId;
    History.pushState({postid: postId}, document.title, loadUrl);
}
function doLoadPost(postId) {
    (function($) {
        var queryObject = $('.et-active-map-slide');
        var queryUrl = "./?p=" + postId;
        var loadedUrl = queryUrl + "&ajax";
        changeActiveMarker(postId);
        panTo(postId);
        queryObject.load(loadedUrl, function() {
            ajaxifyLinks();
            resetShadows();
        });
    })(jQuery);


}

function resizeMap() {
    (function($) {
        var googleMaps = $('#et_main_map');
        var marginTop = $('#main-header').outerHeight();
        googleMaps.css({'margin-top': '0px'});
        var neededHeight = $(window).height() - marginTop;
        googleMaps.height(neededHeight);

    })(jQuery);

}
function loadMapCoordinates(id, callback) {
    (function($) {
        var sql = new cartodb.SQL({user: 'deichblach'});
        var elements = 3;
        var $mapDiv = $('#et_main_map');
        var query = "WITH laterTracks AS (SELECT DISTINCT postId FROM tracks where postId > " + id + " order by postId ASC LIMIT " + elements + "/2) SELECT * FROM TRACKS WHERE postId IN (SELECT postId FROM laterTracks) or postId IN (SELECT DISTINCT postId from Tracks where postId <= " + id + " order by postId desc limit " + elements + " - (select count(*) from laterTracks))";
        sql.getBounds(query).done(function(bounds) {
            //  alert(bounds);
            var google_bounds = new google.maps.LatLngBounds();
            google_bounds.extend(new google.maps.LatLng(bounds[0][0], bounds[0][1]));
            google_bounds.extend(new google.maps.LatLng(bounds[1][0], bounds[1][1]));
            // googleMaps.fitBounds(google_bounds);
            var desiredMapWidth = $mapDiv.width() / 2 - 20;
            var desiredMapHeight = $mapDiv.height() - 180;
            var mapDim = {height: desiredMapHeight, width: desiredMapWidth};
            var zoom = getBoundsZoomLevel(google_bounds, mapDim);
            //Calculate the bounds in px
            var numberOfTiles = 1 << zoom;
            var projection = new MercatorProjection();
            var worldCoordinateSW = projection.fromLatLngToPoint(google_bounds.getSouthWest());
            var worldCoordinateNE = projection.fromLatLngToPoint(google_bounds.getNorthEast());
            var pixelCoordinateSW = new google.maps.Point(worldCoordinateSW.x * numberOfTiles, worldCoordinateSW.y * numberOfTiles);
            var pixelCoordinateNE = new google.maps.Point(worldCoordinateNE.x * numberOfTiles, worldCoordinateNE.y * numberOfTiles);
            var size = new google.maps.Size(pixelCoordinateNE.x - pixelCoordinateSW.x, pixelCoordinateSW.y - pixelCoordinateNE.y);
            var toAddOnBothHorizontally = (desiredMapWidth - size.width) / 2;
            var toAddVertically = (desiredMapHeight - size.height) / 2;
            var newPixelCoordinateSW = new google.maps.Point(pixelCoordinateSW.x - toAddOnBothHorizontally, pixelCoordinateSW.y + toAddVertically);
            var newPixelCoordinateNE = new google.maps.Point(pixelCoordinateNE.x + toAddOnBothHorizontally, pixelCoordinateNE.y - toAddVertically);
            var newWorldCoordinateSW = new google.maps.Point(newPixelCoordinateSW.x / numberOfTiles, newPixelCoordinateSW.y / numberOfTiles);
            var newWorldCoordinateNE = new google.maps.Point(newPixelCoordinateNE.x / numberOfTiles, newPixelCoordinateNE.y / numberOfTiles);
            var newSw = projection.fromPointToLatLng(newWorldCoordinateSW);
            var newNe = projection.fromPointToLatLng(newWorldCoordinateNE);
            var newCenterBounds = new google.maps.LatLngBounds();
            newCenterBounds.extend(new google.maps.LatLng(newNe.lat(), newSw.lng()));
            newCenterBounds.extend(new google.maps.LatLng(newSw.lat(), newSw.lng()));
            callback(newCenterBounds.getCenter(), zoom);

        });
        // alert(result);
    })(jQuery);
}
function panTo(id) {
    (function($) {
        var googleMaps = map;
        loadMapCoordinates(id, function(newCenterBounds, zoom) {
            googleMaps.panTo(newCenterBounds);
            googleMaps.setZoom(zoom);
        });
    })(jQuery);
}


