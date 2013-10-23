// jQuery object
jQuery.fn.sidebardown = function(){
    $(this).click(function () {
        $(this).children().toggleClass("down");
        $(this).siblings().slideToggle("show");
    });
    
}