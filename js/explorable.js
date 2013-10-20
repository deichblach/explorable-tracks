/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
et_active_marker = null;
var map = null;
var et_main_map = null;
function initMap(zoom,center, type) {    
    (function($) {   
        et_main_map = $('#et_main_map');

   
        et_active_marker = null;
        et_main_map.gmap3({
            map: {options: {
                    zoom: zoom,
                    mapTypeId: type,
                    mapTypeControl: true,
                    mapTypeControlOptions: {
                        position: google.maps.ControlPosition.TOP_RIGHT,
                        style: google.maps.MapTypeControlStyle.DEFAULT
                    },
                    
                    panControlOptions: {
                      position: google.maps.ControlPosition.TOP_RIGHT
                    },
                    zoomControlOptions: {
                      position: google.maps.ControlPosition.TOP_RIGHT
                    },
                    navigationControl: false,
                    scrollwheel: true,
                    zoomControl: true,
                    center: center
                    
                } }           
        });
       map = et_main_map.gmap3("get");
       cartodb.createLayer(map, {
  user_name: 'deichblach',
  type: 'cartodb',
  sublayers: [{
    sql: "SELECT * FROM tracks where postid IN "+idCause,
    cartocss: '#tracks {line-width: 3;line-opacity: 0.8;}#tracks[tracktype="bike"] {line-color: #d43838;}#tracks[tracktype="plane"] {line-color: #54aadd;}#tracks[tracktype="car"] {   line-color: #5fd45f;}'
  }]
}).addTo(map)
; // add the layer to our map which already contains 1 sublayer 95c634
          //cartodb_sewer_line.setMap($et_main_map.gmap3("get"));
         })(jQuery);
}
function changeActiveMarker(postId){
    (function($){
        if (et_active_marker) {
            et_active_marker.setAnimation(null);
            et_active_marker.setIcon(notSelectedIcon);
        }       
        et_active_marker = et_main_map.gmap3({get:{id:getMarkerId(postId)}});
        et_active_marker.setAnimation(google.maps.Animation.BOUNCE);
        et_active_marker.setIcon(selectedIcon);        
    })(jQuery);
}
function getMarkerId(postId){
    return 'et_marker_'+postId;
}
function et_add_markers(marker){
    (function($) {
        for(var i=0;i<marker.length;i++){
            marker[i].options = {
                content: '<div id="et_marker_'+marker[i].data.postId+'" class="et_marker_info"><div class="location-description"> <div class="location-title"> <h2>'+marker[i].data.title+'</h2> <div class="listing-info"><p>'+marker[i].data.tags+'</p></div> </div><div class="location-rating"></div> </div> <!-- .location-description --> </div>',
                offset: {
                        y: -42,
                        x: -122
                    }
            };
            marker[i].id = getMarkerId(marker[i].data.postId);
        }
        et_main_map.gmap3({
            marker:{
                values:marker,
                options: {
                    icon: notSelectedIcon
                },
                events:{
                    click: function(marker,event, context) {
                        loadPost(context.data.postId);
                    },
                    mouseover: function(marker, event, context) {
                        if(!context.data.overlay){
                            context.data.overlay = $('#' + getMarkerId(context.data.postId));
                        }
                        context.data.overlay.css({'display': 'block', 'opacity': 0}).stop(true, true).animate({bottom: '15px', opacity: 1}, 500);
                    },
                    mouseout: function(marker, event, context) {
                        context.data.overlay.stop(true, true).animate({bottom: '50px', opacity: 0}, 500, function() {
                            $(this).css({'display': 'none'});
                        });
                    }
                }
            },
            overlay:
            {
                values:marker,
            }
            
        });
    })(jQuery);
}


