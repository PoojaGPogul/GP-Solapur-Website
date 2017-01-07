function mycarousel_initCallback(gallerycarousel)
{
    // Disable autoscrolling if the user clicks the prev or next button.
	gallerycarousel.buttonNext.bind('click', function() {
		gallerycarousel.startAuto(0);
    });
 
	gallerycarousel.buttonPrev.bind('click', function() {
		gallerycarousel.startAuto(0);
    });
 
    // Pause autoscrolling if the user moves with the cursor over the clip.
	gallerycarousel.clip.hover(function() {
		gallerycarousel.stopAuto();
    }, function() {
    	gallerycarousel.startAuto();
    });
};

var simplePhotoGallery = jQuery.noConflict();
simplePhotoGallery(document).ready(function() {
	simplePhotoGallery('#mycarouselPhoto').jcarousel({
        auto: 3,
        wrap: 'last',
        initCallback: mycarousel_initCallback,
        itemFallbackDimension: 300,
        visible:3
    });
}); 

function showPhoto(imageURL)
{
    window.location.href = imageURL;
}

function showsliding(photoURL)
{
    
    window.location.href = photoURL;
}
