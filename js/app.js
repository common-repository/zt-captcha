function a_c_load_another(place){
    clearInp(place);
    var img=document.images[place];               // replace static id [a_c_image] to function parameter for dynamic id get
    img.src=img.src.substring(0,img.src.lastIndexOf("&"))+"&rand="+Math.random()*1000;
}

function clearInp(place) {
    var getplace = place.split("a_c_image_");
    document.getElementById(getplace[1]).value = "";
} 