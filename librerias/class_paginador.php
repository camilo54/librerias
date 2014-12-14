<?php

header("Content-Type: text/html; charset=iso-8859-1");

class paginadormd2 {

    private $_datos;
    private $_paginacion;
    private $_cuerpo;
    private $_encabezado;
    private $_table;
    private $_paginador;

    public function __construct() {
        $this->_datos = array();
        $this->_paginacion = array();
    }

    public function paginar($query, $id = false, $pagina = false, $limite = false) {

        if ($limite && is_numeric($limite)) {
            $limite = $limite;
        } else {
            $limite = 20;
        }

        if ($pagina && is_numeric($pagina)) {
            $pagina = $pagina;
            $inicio = ($pagina - 1) * $limite;
        } else {
            $pagina = 1;
            $inicio = 0;
        }

        $consulta = Mysql::consulta($query);
        $registros = mysql_num_rows($consulta);

        $total = ceil($registros / $limite);
        $query = $query . " LIMIT $inicio, $limite";
        $consulta = Mysql::consulta($query);
        $total_paginado = mysql_num_rows($consulta);
        $total_campos = mysql_num_fields($consulta);

        /* encabezado de la tabla */
        $this->_encabezado .= "<th >Imagen</th>";

        for ($i = 0; $i < $total_campos - 2; $i++) {
            $this->_encabezado .= "<th>" . mysql_field_name($consulta, $i) . "</th>";
        }

        /* id encabezado */
        $this->_encabezado .= "<th  style='text-align: center'>Estado</th>";
        $this->_encabezado .= "<th  >Perfil</th>";
        $this->_encabezado .= "<th  style='text-align: center'>Opciones&nbsp;</th>";

        /* cuerpo de la tabla */

        for ($j = 0; $j < $total_paginado; $j++) {

            $this->_cuerpo .= "<tr>";
            $this->_cuerpo .= '<td><img src="subidas/' . mysql_result($consulta, $j, $id) . '.jpg" class=" img-thumbnail img-responsive" width="70px"></td>';

            for ($k = 0; $k < $total_campos; $k++) {
                $this->_cuerpo .="<td style='vertical-align: middle'>" . mysql_result($consulta, $j, $k) . "</td>";
            }
            $this->_cuerpo .= '<td align="center" style="vertical-align: middle"><button class="btn btn-primary btn-sm"  id="editar" data-id="' . mysql_result($consulta, $j, $id) . '"><i class="fa fa-edit"></i></button>&nbsp;<button class="btn btn-warning btn-sm"  id="clave" data-id="' . mysql_result($consulta, $j, $id) . '"><i class="fa fa-key"></i></button>&nbsp;<button class="btn btn-danger btn-sm" id="eliminar" data-id="' . mysql_result($consulta, $j, $id) . '"><span class="glyphicon glyphicon-trash"></span></button>&nbsp;';
            $this->_cuerpo .= "</tr>";
        }

        $paginacion = array();
        $paginacion['actual'] = $pagina;
        $paginacion['total'] = $total;

        if ($pagina > 1) {
            $paginacion['primero'] = 1;
            $paginacion['anterior'] = $pagina - 1;
        } else {
            $paginacion['primero'] = '';
            $paginacion['anterior'] = '';
        }

        if ($pagina < $total) {
            $paginacion['ultimo'] = $total;
            $paginacion['siguiente'] = $pagina + 1;
        } else {
            $paginacion['ultimo'] = '';
            $paginacion['siguiente'] = '';
        }

        $this->_paginacion = $paginacion;
        return $this->_encabezado;
    }

    public function getRangoPaginacion($limite = false) {
        if ($limite && is_numeric($limite)) {
            $limite = $limite;
        } else {
            $limite = 11;
        }

        $total_paginas = $this->_paginacion['total'];
        $pagina_seleccionada = $this->_paginacion['actual'];
        $rango = ceil($limite / 2);
        $paginas = array();

        $rango_derecho = $total_paginas - $pagina_seleccionada;

        if ($rango_derecho < $rango) {
            $resto = $rango - $rango_derecho;
        } else {
            $resto = 0;
        }

        $rango_izquierdo = $pagina_seleccionada - ($rango + $resto);

        for ($i = $pagina_seleccionada; $i > $rango_izquierdo; $i--) {
            if ($i == 0) {
                break;
            }
            $paginas[] = $i;
        }

        sort($paginas);

        if ($pagina_seleccionada < $rango) {
            $rango_derecho = $limite;
        } else {
            $rango_derecho = $pagina_seleccionada + $rango;
        }

        for ($i = $pagina_seleccionada + 1; $i < $rango_derecho; $i++) {
            if ($i > $total_paginas) {
                break;
            }

            $paginas[] = $i;
        }
        $this->_paginacion['rango'] = $paginas;

        return $this->_paginacion['rango'];
    }

    public function getPaginacion() {
        if ($this->_paginacion) {

            /* PRIMERO */
            if ($this->_paginacion['primero']) {
                $this->_paginador .= '<ul class="pagination pagination-sm"><li><a href="#" data-id="' . $this->_paginacion['primero'] . '" id="primero">Primero</a></li></ul>';
            } else {
                
            }

            /* Anterior */

            if ($this->_paginacion['anterior']) {
                $this->_paginador .= '<ul class="pagination pagination-sm"><li><a href="#" id="anterior" data-id="' . $this->_paginacion['anterior'] . '">Anterior</a></li></ul>';
            } else {
                
            }

            /* For para las paginas */
            for ($i = 0; $i < count($this->getRangoPaginacion()); $i++) {
                if ($this->_paginacion['actual'] != $this->_paginacion['rango'][$i]) {
                    $this->_paginador .= '<ul class="pagination pagination-sm"><li><a  id="pagina" href="#" data-id="' . $this->_paginacion['rango'][$i] . '">' . $this->_paginacion['rango'][$i] . '</a></li></ul>';
                } else {
                    $this->_paginador .= '<ul class="pagination pagination-sm"><li class="active"><a href="#">' . $this->_paginacion['rango'][$i] . '</a></li></ul>';
                }
            }

            /* Siguiente */

            if ($this->_paginacion['siguiente']) {
                $this->_paginador .= '<ul class="pagination pagination-sm"><li><a href="#" data-id="' . $this->_paginacion['siguiente'] . '" id="siguiente">Siguiente</a></li></ul>';
            } else {
                
            }



            /* Ultimo */

            if ($this->_paginacion['ultimo']) {
                $this->_paginador .= '<ul class="pagination pagination-sm"><li><a href="#" data-id="' . $this->_paginacion['ultimo'] . '" id="ultimo">&Uacuteltimo</a></li>';
            } else {
                
            }

            return $this->_paginador;
        } else {
            return false;
        }
    }

    public function tablaBootstrapmd() {
        //me02110864
        $this->_table .= '<table class="table table-condensed table-striped table-responsive table-hover  "  >
            <thead ><tr >' . $this->_encabezado . '</tr></thead>
                <tbody>' . $this->_cuerpo . '</tbody>
                </table>
                <div class="pagination">
                <ul class="pagination pagination-sm"
                ' . $this->getPaginacion() . '
                    </ul></div>
               ';
        return $this->_table;
    }

}
