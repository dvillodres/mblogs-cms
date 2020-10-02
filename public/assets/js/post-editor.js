
/*
 * Este codigo es parte del "plugin" del editor WYSIWYG obtenido en codepen.
*/

$('.toolbar a').click(function(e) {
  var command = $(this).data('command');

  /* 
   * Como no me interesa que se pueda añadir un h1 ya que en las páginas clientes
   * se utilizará como h1 el titulo del post he sustituido h1, h2, h3 por h2, h3, h4
  */

  if (command == 'h2' || command == 'h3' || command == 'h4' || command == 'p') {
    document.execCommand('formatBlock', false, command);
  }
  
  if (command == 'forecolor' || command == 'backcolor') {
    document.execCommand($(this).data('command'), false, $(this).data('value'));
  }
  
  if (command == 'createlink') {
  url = prompt('Introduce el enlace: ','http:\/\/'); document.execCommand($(this).data('command'), false, url);
  }

  if (command == 'insertimage') {
  url = prompt('Introduce la ruta de la imagen: ','http:\/\/');
  document.execCommand($(this).data('command'), false, url);
  }

  else document.execCommand($(this).data('command'), false, null);
});

$('#editor').bind('blur keyup paste copy cut mouseup', function(e) {
  update_output();
})

function update_output() {
  $('#output').val($('#editor').html());
}


/*
 * A continuación he modificado el documento para que la 
 * separación de los párrafos la haga con elementos p y 
 * no con div que es como lo hacía por defecto el plugin.
*/


  document.execCommand('defaultParagraphSeparator', false, 'p');