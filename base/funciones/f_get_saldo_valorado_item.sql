--------------- SQL ---------------

CREATE OR REPLACE FUNCTION alm.f_get_saldo_valorado_item (
  p_id_item integer,
  p_id_almacen integer
)
RETURNS numeric AS
$body$
/**************************************************************************
 SISTEMA:		SISTEMA DE ALMACENES
 FUNCION: 		alm.f_get_saldo_valorado_item
 DESCRIPCION:   Función que devuelve la cantidad valorada existente del item con ID: p_id_item
 RETORNA:		Devuelve el valor de la cantidad disponible para el item: p_id_item
 AUTOR: 		Ariel Ayaviri Omonte
 FECHA:	        21/02/2013
 COMENTARIOS:	
***************************************************************************/

DECLARE

	v_nombre_funcion	text;
    v_resp				varchar;
    v_ingresos			numeric;
  	v_salidas			numeric;
    v_saldo_valorado	numeric;

BEGIN
    v_nombre_funcion = 'alm.f_get_saldo_valorado_item';
    
    select sum(movdet.cantidad * movdet.costo_unitario) into v_ingresos
    from alm.tmovimiento_det movdet
    inner join alm.tmovimiento mov on mov.id_movimiento = movdet.id_movimiento
    inner join alm.tmovimiento_tipo movtip on movtip.id_movimiento_tipo = mov.id_movimiento_tipo
    where movdet.estado_reg = 'activo'
        and movtip.tipo like '%ingreso%'
        and movdet.cantidad is not null
        and movdet.costo_unitario is not null
        and movdet.id_item = p_id_item
        and mov.estado_mov = 'finalizado'
        and mov.id_almacen = p_id_almacen;
    
    select sum(movdet.cantidad * movdet.costo_unitario) into v_salidas
    from alm.tmovimiento_det movdet
    inner join alm.tmovimiento mov on mov.id_movimiento = movdet.id_movimiento
    inner join alm.tmovimiento_tipo movtip on movtip.id_movimiento_tipo = mov.id_movimiento_tipo
    where movdet.estado_reg = 'activo'
        and movtip.tipo like '%salida%'
        and movdet.cantidad is not null
        and movdet.costo_unitario is not null
        and movdet.id_item = p_id_item
        and mov.estado_mov = 'finalizado'
        and mov.id_almacen = p_id_almacen;
    
    if (v_ingresos is null) then
    	v_saldo_valorado = 0;
    elseif (v_salidas is null) then
    	v_saldo_valorado = v_ingresos;
    else
    	v_saldo_valorado = v_ingresos - v_salidas;
    end if;
    
    return v_saldo_valorado;
EXCEPTION					
	WHEN OTHERS THEN
			v_resp='';
			v_resp = pxp.f_agrega_clave(v_resp,'mensaje',SQLERRM);
			v_resp = pxp.f_agrega_clave(v_resp,'codigo_error',SQLSTATE);
			v_resp = pxp.f_agrega_clave(v_resp,'procedimientos',v_nombre_funcion);
			raise exception '%',v_resp;
END;
$body$
LANGUAGE 'plpgsql'
VOLATILE
CALLED ON NULL INPUT
SECURITY INVOKER
COST 100;