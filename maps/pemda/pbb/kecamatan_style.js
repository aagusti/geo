function styleitem() {
  //Make symbol size 14 or 7
  var size = shape.attributes.NAME.length > 10 ? 14:7;

  var style = "STYLE SIZE " + size + " SYMBOL 'circle'";

  var red = Math.random()*255;
  var green = Math.random()*255;
  var blue = Math.random()*255;
  style += "COLOR " + red + " " + green + " " + blue + " END";

  //Return style to MapServer
  return style;
}