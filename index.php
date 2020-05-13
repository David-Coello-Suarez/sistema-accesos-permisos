<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pruebas de Accesos</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
</head>

<body>
    <div class="container">
        <div class="col-md-12">
            <form role="form" class="form-inline" id="formAcceso">
                <div class="form-group">
                    <input type="text" id="usuario" value="administrador" disabled class="form-control">
                </div>
                <button type="submit" class="btn btn-sm btn-success" id="guardarUsuario">Guardar</button>
            </form>
            <div class="col-md-offset-3 col-md-9" id="sys_menu"></div>
        </div>
    </div>
</body>
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script type="text/javascript">
    function checkear() {
        $('input[type="checkbox"]').change(function(e) {

            var checked = $(this).prop("checked"),
                container = $(this).parent(),
                siblings = container.siblings();

            container.find('input[type="checkbox"]').prop({
                indeterminate: false,
                checked: checked
            });

            function checkSiblings(el) {

                var parent = el.parent().parent(),
                    all = true;

                el.siblings().each(function() {
                    let returnValue = all = ($(this).children('input[type="checkbox"]').prop("checked") === checked);
                    return returnValue;
                });

                if (all && checked) {

                    parent.children('input[type="checkbox"]').prop({
                        indeterminate: false,
                        checked: checked
                    });

                    checkSiblings(parent);

                } else if (all && !checked) {

                    parent.children('input[type="checkbox"]').prop("checked", checked);
                    parent.children('input[type="checkbox"]').prop("indeterminate", (parent.find('input[type="checkbox"]:checked').length > 0));
                    checkSiblings(parent);

                } else {

                    el.parents("li").children('input[type="checkbox"]').prop({
                        indeterminate: true,
                        checked: false
                    });

                }

            }

            checkSiblings(container);
        });
    }


    /**
     * -hago la peticion a la base de datos
     * e itero sobre cada uno para ir ordenadolos desde padres e hijos con su respectivos permisos
     * en cada modulo
     */
    $.ajax({
        async: true,
        type: 'POST',
        url: 'util/accesos/query.php',
        dataType: 'json',
        error: function(mensaje) {
            console.log(mensaje.responseText);
        },
        beforeSend: function() {
            $("#sys_menu").empty();
        },
        success: function(respuesta) {

            switch (respuesta.estado) {
                case 1:
                    var menu = respuesta.data;
                    var sys_menu = "<ul>";
                    for (var m = 0; m < menu.length; m++) {
                        sys_menu += "<li>";
                        sys_menu += "<input type='checkbox' id='menu_" + (m + 1) + "' value='" + menu[m]['idmenu'] + "' class='menu_sys' />";
                        sys_menu += " <label for='menu_" + (m + 1) + "'>" + menu[m]['nombre'] + "</label>";

                        var submenu = menu[m]['submenu'];
                        sys_menu += "<ul>";
                        for (var sm = 0; sm < submenu.length; sm++) {
                            sys_menu += "<li>";
                            sys_menu += "<input type='checkbox' id='menu_" + (m + 1) + "_" + (sm + 1) + "' value='" + submenu[sm]['idmenu'] + "' class='menu_sys' />";
                            sys_menu += " <label for='menu_" + (m + 1) + "_" + (sm + 1) + "'>" + submenu[sm]['nombre'] + "</label>";

                            permisos = submenu[sm]['permisos'];
                            sys_menu += "<ul>";
                            var p = 0;

                            Object.entries(permisos).forEach(function([indice, value]) {
                                sys_menu += "<li>";
                                sys_menu += "<input type='checkbox' id='menu_" + (m + 1) + "_" + (sm + 1) + "_" + (p + 1) + "' value='" + submenu[sm]['idmenu'] + "' " + (value == 1 ? 'checked' : '') + " class='menu_acc' />";
                                sys_menu += " <label for='menu_" + (m + 1) + "_" + (sm + 1) + "_" + (p + 1) + "' class='text-capitalize'>" + indice + "</label>";
                                sys_menu += "</li>";
                                p++;
                            });

                            sys_menu += "</ul>";
                            sys_menu += "</li>";
                        }
                        sys_menu += "</ul>";

                        sys_menu += "</li>";
                    }
                    sys_menu += "</ul>";
                    $('#sys_menu').html(sys_menu);
                    break;
            }
        },
        complete: function() {
            checkear();
        }
    });

    $('#formAcceso').submit(function(event) {
        event.preventDefault();
        var usuario = $("#usuario").val();
        if (usuario != null) {
            /**
             * Aqui crea un array normal
             * contador de ventana para ir colocando dentro de ellas un nuevo array
             */
            var menuListo = [];
            var cont_ventana = 0;
            var existe_menu = false;

            /**
             * itero sobre los checkbox padre e hijos que esten checkeaod o indeterminado
             */
            $('.menu_sys').each(function() {
                existe_menu = true;
                var este = this,
                    valor = este.value,
                    cont_pos = 2;
                var checkear = ($(este).is(':checked') || $(este).prop('indeterminate') ? 1 : 0);
                if (checkear == 1) {
                    /** 
                     * justo aqui se crea un array bidimencional para cada uno de lo checkbox padres e hijos
                    */
                    menuListo[cont_ventana] = [];
                    menuListo[cont_ventana][0] = valor; //Aqui guardo el id de ventana 
                    menuListo[cont_ventana][1] = usuario; // al usuario que va a tener el permisos

                    /**
                     * con este for añado dos valor 0 solo para la ventana del modulo
                     * En la base de datos en la ventanas donde se localizan los modulos solo aparecera 0 eso no afecta al modulo 
                     * correspondiente
                     */
                    var aux = 2;
                    for (let [i, v] in Object.entries(permisos)) {
                        menuListo[cont_ventana][aux] = 0;
                        aux++;
                    }

                    /**
                     * Aqui itero sobre los permisos que va a tener el usuario sobre el modulo por ejemplo: crear, ejecutar, etc
                     */
                    $('.menu_acc').each(function() {
                        var permiso = this;
                        if (valor == permiso.value) {
                            menuListo[cont_ventana][cont_pos] = ($(permiso).is(':checked') ? 1 : 0);
                        } else {
                            cont_pos = 1;
                        }

                        cont_pos++;
                    });

                }
                cont_ventana++;
            });

            /**
             * aqui se verfifica que existe el menu y se envia a php para guardar en la base de datos
             */

            if (existe_menu) {
                $.ajax({
                    async: false,
                    type: 'POST',
                    url: 'util/accesos/gestion.php',
                    dataType: 'json',
                    data: {
                        usuario: usuario,
                        menuChekeado: menuListo
                    },
                    error: function(error) {
                        console.log(error.responseText);
                    },
                    success: function(respuesta) {
                        console.log(respuesta);
                    }
                });
            }
        }
        return false;
    });
</script>

</html>