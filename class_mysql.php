<?php

class Mysql {

    public static function Conectar() {
        if (!$link = mysql_connect(SERVER, USER, PASS)) {
            die("Error en el servidor");
        }

        if (!mysql_select_db(BD)) {
            die("Error al Conectar con la base de datos");
        }

        return $link;
    }

    public static function consulta($query) {
        if (!$sql = mysql_query($query, Mysql::Conectar())) {
            die(mysql_error());
        }
        return $sql;
    }

}

class MysqlQuery {

    public static function limpiarCadena($valor) {
        $valor = str_ireplace("SELECT", "", $valor);
        $valor = str_ireplace("COPY", "", $valor);
        $valor = str_ireplace("DELETE", "", $valor);
        $valor = str_ireplace("DROP", "", $valor);
        $valor = str_ireplace("DUMP", "", $valor);
        $valor = str_ireplace(" OR ", "", $valor);
        $valor = str_ireplace("%", "", $valor);
        $valor = str_ireplace("LIKE", "", $valor);
        $valor = str_ireplace("--", "", $valor);
        $valor = str_ireplace("^", "", $valor);
        $valor = str_ireplace("[", "", $valor);
        $valor = str_ireplace("]", "", $valor);
        $valor = str_ireplace("\\", "", $valor);
        $valor = str_ireplace("!", "", $valor);
        $valor = str_ireplace("¡", "", $valor);
        $valor = str_ireplace("?", "", $valor);
        $valor = str_ireplace("=", "", $valor);
        $valor = str_ireplace("&", "", $valor);
        return $valor;
    }

    public static function RequestGet($val) {
        $data = addslashes($_GET[$val]);
        $var = utf8_decode($data);
        $datos = MysqlQuery::limpiarCadena($var);
        return $datos;
    }

    public static function RequestPost($val) {
        $data = $_POST[$val];
        $datos = MysqlQuery::limpiarCadena($data);
        $valores = mysql_escape_string($datos);
        $string = htmlentities($valores);
        return $string;
    }

    public static function RequestCaracteres($val) {
        $data = $_POST[$val];
        $valores = mysql_escape_string($data);
        $string = htmlentities($valores);
        return $string;
    }

    public static function Guardar($tabla, $campos, $valores) {
        if (!$sql = Mysql::consulta("insert into $tabla ($campos) VALUES($valores)", Mysql::Conectar())) {
            die("Error al insertar los datos en la tabla $tabla");
        }

        return $sql;
    }

    public static function Eliminar($tabla, $condicion) {
        if (!$sql = Mysql::consulta("DELETE FROM $tabla WHERE $condicion")) {
            die("Error al eliminar registros en la tabla $tabla");
        }

        return $sql;
    }

    public static function operaciones($opera, $columna, $tabla, $id_e) {
        if (!$sql = Mysql::consulta("SELECT $opera ($columna) FROM $tabla where id_encuesta='$id_e'")) {
            die("Error al realizar operación en la tabla $tabla");
        }
        $relust = mysql_result($sql, 0, 0);
        return $relust;
    }

    public static function Actualizar($tabla, $campos, $condicion) {
        if (!$sql = Mysql::consulta("update $tabla set $campos where $condicion")) {
            die("Error al actualizar datos en la tabla $tabla");
        }
        return $sql;
    }

    public static function Session($tabla, $user, $id, $condicion) {
        $sql = Mysql::consulta("select * from $tabla where $condicion");
        $num_reg = mysql_num_rows($sql);
        $reg = mysql_fetch_assoc($sql);
        if ($num_reg > 0) {
            session_start();
            $_SESSION['usuario'] = mysql_result($sql, 0, $user);
            $_SESSION['id_usuario'] = mysql_result($sql, 0, $id);
            $_SESSION['id_perfil'] = $reg['id_perfil'];
            $_SESSION['nombre'] = $reg['nombre'];
            $_SESSION["ultimoAcceso"] = date("Y-n-j H:i:s");
            $_SESSION['auntenticado'] = "si";
            
            if($_SESSION['id_perfil']==1){
                echo 1;
            }elseif ($_SESSION['id_perfil']==2) {
                echo 2;
            }elseif ($_SESSION['id_perfil']==3) {
                echo 3;
            }
            
        } else {

            die("Error en los datos de auntenticacion");
        }
    }

    public static function Comprobar_Session($url) {
        session_start();
        if ($_SESSION['auntenticado'] != "si") {
            header("Location: $url");
            echo 2;
        } else {
            return $_SESSION['usuario'];
        }
    }

    public static function Session_inactiva($url) {
        if ($_SESSION["auntenticado"] != "si") {
            //si no está logueado lo envío a la página de autentificación
            header("Location: $url");
        } else {
            //sino, calculamos el tiempo transcurrido
            $fechaGuardada = $_SESSION["ultimoAcceso"];
            $ahora = date("Y-n-j H:i:s");
            $tiempo_transcurrido = (strtotime($ahora) - strtotime($fechaGuardada));

            //comparamos el tiempo transcurrido
            if ($tiempo_transcurrido >= 600) {
                //si pasaron 10 minutos o más
                session_destroy(); // destruyo la sesión
                header("Location: $url"); //envío al usuario a la pag. de autenticación
                //sino, actualizo la fecha de la sesión
            } else {
                $_SESSION["ultimoAcceso"] = $ahora;
            }
        }
    }

    public static function GenerarSelect($tabla, $campo, $id) {
        $select = "<select name='$campo' required>"
                . "<option value=''>Selecciona:</option>";

        $sql = Mysql::consulta("select $campo,$id from $tabla");

        for ($i = 0; $i < mysql_num_rows($sql); $i++) {
            $select .= "<option value='" . mysql_result($sql, $i, $id) . "'>" . mysql_result($sql, $i, $campo) . "</option>";
        }

        $select .="</select>";
        return $select;
    }

    public static function buscarvalor($columna, $tabla, $condicion) {
        if (!$sql = Mysql::consulta("SELECT ($columna) FROM $tabla where $condicion")) {
            die("Error al eliminar registros en la tabla $tabla");
        }
        $relust = mysql_result($sql, 0, 0);
        return $relust;
    }

    public static function cuentacampos($campo, $tabla, $condicion) {
        if (!$sql = Mysql::consulta("SELECT count($campo) FROM $tabla where $condicion")) {
            die("Error al eliminar registros en la tabla $tabla");
        }
        $relust = mysql_result($sql, 0, 0);
        return $relust;
    }

    public static function RequestClave($val) {
        $data = addslashes($_POST[$val]);
        $var = utf8_decode($data);
        $datos = MysqlQuery::limpiarCadena($var);
        $encriptar = md5($datos);
        return $encriptar;
    }

}
