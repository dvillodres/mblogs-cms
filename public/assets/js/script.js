  $(document).ready(function(){
    $('.sidenav').sidenav();
  });
  
  document.addEventListener('DOMContentLoaded', function() {
    var elems = document.querySelectorAll('select');
    var instances = M.FormSelect.init(elems, options);
  });

  $(document).ready(function(){
    $('select').formSelect();
  });

  
  // Marcamos activo el botón del menú que corresponde a la url

  var url = window.location.href.split('/');
  url = url[url.length - 1];
  $("a[href$='" + url + "']").addClass('active');


