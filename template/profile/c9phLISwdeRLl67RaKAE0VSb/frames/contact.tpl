<div class="frame">
    
    
    
    <?php echo $class_post->cb('header_caption',['default'=>"<h1>Contact Us</h1><P>This is a default content block.</P>"]); ?>
    
    <div class="coltable col3 vtop box-container">
        <div class="col">
            <div class="box">
                <?php echo $class_post->cb('info_column_1',['default'=>"<h3>Contacts</h3><p>Read more about our company.</p>"]); ?>
            </div>
        </div>
        <div class="col">
            <div class="box">
                <?php echo $class_post->cb('info_column_2',['default'=>"<h3>Address</h3><p>Read more about our company.</p>"]); ?>
            </div>
        </div>
        <div class="col">
            <div class="box">
                <?php echo $class_post->cb('info_column_3',['default'=>"<h3>Hours</h3><p>Read more about our company.</p>"]); ?>
            </div>
        </div>
    </div>
    
</div><!--top section-->

<div class="section section-contact-form">

	<div class="frame">
    	
        <div class="column col-center w6">
        	
            <?php echo $class_website->form_build('form_contact'); ?>
            
        </div>
        
    </div>

</div><!--contact form-->

<?php if($setting['ws_addr_city']!=NULL||$setting['ws_addr_suburb']!=NULL) { ?>
<div class="section section-contact-map">

	<h2>Map of <?php echo $zulu->compile(', ',[$setting['ws_addr_addr'],$setting['ws_addr_suburb'],$setting['ws_addr_city'],$setting['ws_addr_country']]); ?></h2>
	<div class="map" id="map_canvas">
    	
    </div>

</div><!--contact form-->
<script type="text/javascript">
$(document).ready(function() {
	initialize_map("<?php echo $setting['ws_addr_addr']; ?>, <?php echo $setting['ws_addr_suburb']; ?>, <?php echo $setting['ws_addr_city']; ?>, <?php echo $setting['ws_addr_country']; ?>");
});

var map;
var geocoder;
var location_to_use;

function initialize_map(address_to_geo) {
	geocoder = new google.maps.Geocoder();
	
	var address = address_to_geo;
	geocoder.geocode( { 'address': address}, function(results, status) {
	  if (status == google.maps.GeocoderStatus.OK) {
		location_to_use = results[0].geometry.location;
		
			var myOptions = {
				zoom: 13,
				center: location_to_use,
				mapTypeId: google.maps.MapTypeId.ROADMAP
			};
			var map = new google.maps.Map(document.getElementById("map_canvas"),myOptions);
			
			var marker = new google.maps.Marker({
				position: location_to_use,
				map: map
			});
	
		
	  } else {
		  //do nothing as of v3.1.3
	  }
	});

}
</script>
<script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?key=AIzaSyBGikaBtoB6uU1vW5TcFnfI2e_8WY8r4Y8&sensor=false"></script>
<?php } ?>