//Applies behaviour rules to the classes
//Author: Brian R Miedlar (c) 2006-2009

var AppBehavior = Class.create();
AppBehavior.Load = function() {
    OS.RegisterBehaviour(AppBehavior.CarouselRules);
}
AppBehavior.CarouselRules = {

	    '#express-crosssell': function(element) {
	        //Pictures
	        AppBehavior.PictureCarousel = new Carousel('crossel-carousel', element, 223, 78, AppBehavior, {
	        	showSize:4,
	        	scroll:1,
	            setSize: 2,
	            duration: .5,
	            direction: 'horizontal',
	            itemParser: function(item) {
	                //Given html element you can build a data object for the item if needed for later activatio
	                var sKey = item.down('.key').innerHTML;
	                var sCaption = item.down('.caption').innerHTML;
	                var sPictureHtml = item.down('.picture').innerHTML;
	                return { name: sCaption, pictureHtml: sPictureHtml };
	            },
	            setItemEvents: function(carousel, itemElement, carouselItem, observer) {
	                //This allows you to set events to the item like rollovers/mouse events
	                Event.observe(itemElement, 'click', function() {
	                    carousel.activate(carouselItem);
	                });
	            },
	            allowAutoLoopOnSet: true,
	            allowAutoLoopOnIndividual: false
	        });
	        AppBehavior.PictureCarousel.load();
	    },
	    
    '#express-brands': function(element) {
        //Pictures
        AppBehavior.PictureCarousel = new Carousel('PictureCarousel', element, 112, 33, AppBehavior, {
            setSize: 8,
            duration: .5,
            direction: 'horizontal',
            itemParser: function(item) {
                //Given html element you can build a data object for the item if needed for later activatio
                var sKey = item.down('.key').innerHTML;
                var sCaption = item.down('.caption').innerHTML;
                var sPictureHtml = item.down('.picture').innerHTML;
                return { name: sCaption, pictureHtml: sPictureHtml };
            },
            setItemEvents: function(carousel, itemElement, carouselItem, observer) {
                //This allows you to set events to the item like rollovers/mouse events
                Event.observe(itemElement, 'click', function() {
                    carousel.activate(carouselItem);
                });
            },
            allowAutoLoopOnSet: true,
            allowAutoLoopOnIndividual: false
        });
        AppBehavior.PictureCarousel.load();
    },
    
    '#featured-products-carousel': function(element) {
        //Pictures
        AppBehavior.PictureCarousel = new Carousel('PictureCarouse3', element, 170, 170, AppBehavior, {
            setSize: 3,
            duration: .5,
            direction: 'horizontal',
            itemParser: function(item) {
                //Given html element you can build a data object for the item if needed for later activatio
                var sKey = item.down('.key').innerHTML;
                var sCaption = item.down('.caption').innerHTML;
                var sPictureHtml = item.down('.picture').innerHTML;
                return { name: sCaption, pictureHtml: sPictureHtml };
            },
            setItemEvents: function(carousel, itemElement, carouselItem, observer) {
                //This allows you to set events to the item like rollovers/mouse events
                Event.observe(itemElement, 'click', function() {
                    carousel.activate(carouselItem);
                });
            },
            allowAutoLoopOnSet: true,
            allowAutoLoopOnIndividual: false
        });
        AppBehavior.PictureCarousel.load();
    },
    
    
        '#express-images': function(element) {
        //Pictures
        AppBehavior.PictureCarousel = new Carousel('PictureCarouse2', element, 48, 48, AppBehavior, {
            setSize: 5,
            duration: .5,
            direction: 'horizontal',
            itemParser: function(item) {
                //Given html element you can build a data object for the item if needed for later activatio
                var sKey = item.down('.key').innerHTML;
                var sCaption = item.down('.caption').innerHTML;
                var sPictureHtml = item.down('.picture').innerHTML;
                return { name: sCaption, pictureHtml: sPictureHtml };
            },
            setItemEvents: function(carousel, itemElement, carouselItem, observer) {
                //This allows you to set events to the item like rollovers/mouse events
                Event.observe(itemElement, 'click', function() {
                    carousel.activate(carouselItem);
                });
            },
            allowAutoLoopOnSet: true,
            allowAutoLoopOnIndividual: false
        });
        AppBehavior.PictureCarousel.load();
    },
    
    '#Cmd_NextItem': function(element) {
        Event.observe(element, 'click', function() {         
            AppBehavior.ProfileCarousel.next();
        });
    },
    '#Cmd_PreviousItem': function(element) {
        Event.observe(element, 'click', function() {
            AppBehavior.ProfileCarousel.previous();
        });
    }
    
}

//EVENT OBSERVATION
AppBehavior.fireActiveCarouselLoaded = function(carousel) {
	if (carousel.key=="crossel-carousel") {	 
		 setInterval(function(){			  
			carousel.autonext();	
		 },7000);
	}
	 
}
AppBehavior.fireActiveCarouselItem = function(carousel, element, item) {
    element.addClassName('selected');

    // Here we can update any part of the DOM to represent our data
    // In this sample we will use the same generic viewer element for all carousels
    switch(carousel.key) {
        case 'ProfileCarousel': 
            $('ViewerCaption').update(item.value.name);
            $('ViewerData').update(item.value.email);
            Element.show('Viewer');
            break;
            
        case 'PictureCarousel':
            $('ViewerCaption').update(item.value.name);
            $('ViewerData').update(item.value.pictureHtml);
            Element.show('Viewer');
            break;

        case 'GroupCarousel':
            $('ViewerCaption').update(item.value.name);
            $('ViewerData').update(item.value.email);
            Element.show('Viewer');
            break;
    }
}
AppBehavior.fireDeactiveCarouselItem = function(carousel, element, item) {
    element.removeClassName('selected');

    switch(carousel.key) {
        case 'ProfileCarousel': 
            Element.hide('Viewer');
            break;

        case 'PictureCarousel': 
            Element.hide('Viewer');
            break;

        case 'GroupCarousel': 
            Element.hide('Viewer');
            break;
    }
}

AppBehavior.Load();
	