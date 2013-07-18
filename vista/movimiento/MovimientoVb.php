<?php
/**
*@package pXP
*@file MovimientoVb.php
*@author  Gonzalo Sarmiento
*@date 11-07-2013 10:22:05
*@description Archivo con la interfaz de usuario que permite la ejecucion de todas las funcionalidades del sistema
*/
header("content-type: text/javascript; charset=UTF-8");
?>
<script>
Phx.vista.MovimientoVb = {
    bedit:false,
    bnew:false,
    bsave:false,
    bdel:false,
	require:'../../../sis_almacenes/vista/movimiento/Movimiento.php',
	requireclase:'Phx.vista.Movimiento',
	title:'MovimientoVb',
	nombreVista: 'movimientoVb',
	
	constructor: function(config) {
	    
	    this.maestro=config.maestro;
	            
    	Phx.vista.MovimientoVb.superclass.constructor.call(this,config);
    	this.addButton('ini_estado',{argument: {estado: 'inicio'},text:'Dev. a Borrador',iconCls: 'batras',disabled:true,handler:this.antEstado,tooltip: '<b>Retorna Movimiento al estado borrador</b>'});
     this.addButton('ant_estado',{argument: {estado: 'anterior'},text:'Anterior',iconCls: 'batras',disabled:true,handler:this.antEstado,tooltip: '<b>Pasar al Anterior Estado</b>'});
     this.addButton('sig_estado',{text:'Siguiente',iconCls: 'badelante',disabled:true,handler:this.sigEstado,tooltip: '<b>Pasar al Siguiente Estado</b>'});
     
     this.getBoton('btnRevertir').hide();
     this.getBoton('btnCancelar').hide();
        
        //formulario para preguntar sobre siguiente estado
        //agrega ventana para selecion de RPC
                
            this.formEstado = new Ext.form.FormPanel({
            baseCls: 'x-plain',
            autoDestroy: true,
           
            border: false,
            layout: 'form',
            autoHeight: true,
           
    
            items: [
                {
                    xtype: 'combo',
                    name: 'id_tipo_estado',
                      hiddenName: 'id_tipo_estado',
                    fieldLabel: 'Siguiente Estado',
                    listWidth:280,
                    allowBlank: false,
                    emptyText:'Elija el estado siguiente',
                    store:new Ext.data.JsonStore(
                    {
                        url: '../../sis_workflow/control/TipoEstado/listarTipoEstado',
                        id: 'id_tipo_estado',
                        root:'datos',
                        sortInfo:{
                            field:'tipes.codigo',
                            direction:'ASC'
                        },
                        totalProperty:'total',
                        fields: ['id_tipo_estado','codigo_estado','nombre_estado'],
                        // turn on remote sorting
                        remoteSort: true,
                        baseParams:{par_filtro:'tipes.nombre_estado#tipes.codigo'}
                    }),
                    valueField: 'id_tipo_estado',
                    displayField: 'codigo_estado',
                    forceSelection:true,
                    typeAhead: false,
                    triggerAction: 'all',
                    lazyRender:true,
                    mode:'remote',
                    pageSize:50,
                    queryDelay:500,
                    width:210,
                    gwidth:220,
                    minChars:2,
                    tpl: '<tpl for="."><div class="x-combo-list-item"><p>{codigo_estado}</p>Prioridad: <strong>{nombre_estado}</strong> </div></tpl>'
                
                },
                {
                    xtype: 'combo',
                    name: 'id_funcionario_wf',
                    hiddenName: 'id_funcionario_wf',
                    fieldLabel: 'Funcionario Resp.',
                    allowBlank: false,
                    emptyText:'Elija un funcionario',
                    listWidth:280,
                    store:new Ext.data.JsonStore(
                    {
                        url: '../../sis_workflow/control/TipoEstado/listarFuncionarioWf',
                        id: 'id_funcionario',
                        root:'datos',
                        sortInfo:{
                            field:'prioridad',
                            direction:'ASC'
                        },
                        totalProperty:'total',
                        fields: ['id_funcionario','desc_funcionario','prioridad'],
                        // turn on remote sorting
                        remoteSort: true,
                        baseParams:{par_filtro:'fun.desc_funcionario1'}
                    }),
                    valueField: 'id_funcionario',
                    displayField: 'desc_funcionario',
                    forceSelection:true,
                    typeAhead: false,
                    triggerAction: 'all',
                    lazyRender:true,
                    mode:'remote',
                    pageSize:50,
                    queryDelay:500,
                    width:210,
                    gwidth:220,
                    minChars:2,
                    tpl: '<tpl for="."><div class="x-combo-list-item"><p>{desc_funcionario}</p>Prioridad: <strong>{prioridad}</strong> </div></tpl>'
                
                },
                    {
                        name: 'obs',
                        xtype: 'textarea',
                        fieldLabel: 'Intrucciones',
                        allowBlank: false,
                        anchor: '80%',
                        maxLength:500
                    },
                  {
                    xtype: 'combo',
                    name:'instruc_rpc',
                    fieldLabel:'Proceder',
                    allowBlank:false,
                    emptyText:'Tipo...',
                    typeAhead: true,
                    triggerAction: 'all',
                    lazyRender:true,
                    mode: 'local',
                    valueField: 'estilo',
                    gwidth: 100,
                    store:['Iniciar Contrato','Orden de Bien/Servicio','Cotizar','Solicitar Pago']
                }]
        });
        
        
         this.wEstado = new Ext.Window({
            title: 'Estados',
            collapsible: true,
            maximizable: true,
             autoDestroy: true,
            width: 380,
            height: 290,
            layout: 'fit',
            plain: true,
            bodyStyle: 'padding:5px;',
            buttonAlign: 'center',
            items: this.formEstado,
            modal:true,
             closeAction: 'hide',
            buttons: [{
                text: 'Guardar',
                 handler:this.confSigEstado,
                scope:this
                
            },
             {
                    text: 'Guardar',
                    handler:this.antEstadoSubmmit,
                    scope:this
                    
             },
             {
                text: 'Cancelar',
                handler:function(){this.wEstado.hide()},
                scope:this
            }]
        });
        
        
        this.store.baseParams={tipo_interfaz:this.nombreVista};
        this.load({params:{start:0, limit:this.tam_pag}}); 
        
        this.cmbTipoEstado =this.formEstado.getForm().findField('id_tipo_estado');
        this.cmbTipoEstado.store.on('loadexception', this.conexionFailure,this);
     
        this.cmbFuncionarioWf =this.formEstado.getForm().findField('id_funcionario_wf');
        this.cmbFuncionarioWf.store.on('loadexception', this.conexionFailure,this);
      
        this.cmpObs=this.formEstado.getForm().findField('obs');
        
        this.cmbIntrucRPC =this.formEstado.getForm().findField('instruc_rpc');
       
        
        this.cmbTipoEstado.on('select',function(){
            
            this.cmbFuncionarioWf.enable();
            this.cmbFuncionarioWf.store.baseParams.id_tipo_estado = this.cmbTipoEstado.getValue();
            this.cmbFuncionarioWf.modificado=true;
        },this);
        
        
		
	},
	confSigEstado :function() {                   
            var d= this.sm.getSelected().data;
           
            if ( this.formEstado .getForm().isValid()){
                 Phx.CP.loadingShow();
                    Ext.Ajax.request({
                        // form:this.form.getForm().getEl(),
                        url:'../../sis_almacenes/control/Movimiento/siguienteEstadoMovimiento',
                        params:{
                            id_movimiento:d.id_movimiento,
                            operacion:'cambiar',
                            id_tipo_estado:this.cmbTipoEstado.getValue(),
                            id_funcionario:this.cmbFuncionarioWf.getValue(),
                            obs:this.cmpObs.getValue(),
                            instruc_rpc:this.cmbIntrucRPC.getValue()
                            },
                        success:this.successSinc,
                        failure: this.conexionFailure,
                        timeout:this.timeout,
                        scope:this
                    }); 
              }    
        },   
    
    sigEstado:function()
        {                   
            var d= this.sm.getSelected().data;
           
            Phx.CP.loadingShow();
            this.cmbTipoEstado.reset();
            this.cmbFuncionarioWf.reset();
            this.cmbFuncionarioWf.store.baseParams.id_estado_wf=d.id_estado_wf;
            this.cmbFuncionarioWf.store.baseParams.fecha=d.fecha_mov;
            
            this.cmbTipoEstado.show();
            this.cmbFuncionarioWf.show();
            this.cmbTipoEstado.enable();
         
            Ext.Ajax.request({
                // form:this.form.getForm().getEl(),
                url:'../../sis_almacenes/control/Movimiento/siguienteEstadoMovimiento',
                params:{id_movimiento:d.id_movimiento,
                        operacion:'verificar',
                        obs:this.cmpObs.getValue()},
                success:this.successSinc,
                argument:{data:d},
                failure: this.conexionFailure,
                timeout:this.timeout,
                scope:this
            });     
        },
       
      antEstado:function(res,eve) {                   
            this.wEstado.buttons[0].hide();
            this.wEstado.buttons[1].show();
            this.wEstado.show();
            
            this.cmbTipoEstado.hide();
            this.cmbFuncionarioWf.hide();
            this.cmbTipoEstado.disable();
            this.cmbFuncionarioWf.disable();
            this.cmbIntrucRPC.hide();
            this.cmbIntrucRPC.disable(); 
            this.cmpObs.setValue('');
            
            this.sw_estado =res.argument.estado;
           
               
        },
        
        antEstadoSubmmit:function(res){
            var d= this.sm.getSelected().data;
           
            Phx.CP.loadingShow();
            var operacion = 'cambiar';
            operacion=  this.sw_estado == 'inicio'?'inicio':operacion; 
            
            Ext.Ajax.request({
                // form:this.form.getForm().getEl(),
                url:'../../sis_almacenes/control/Movimiento/anteriorEstadoMovimiento',
                params:{id_movimiento:d.id_movimiento, 
                        id_estado_wf:d.id_estado_wf, 
                        operacion: operacion,
                        obs:this.cmpObs.getValue()},
                success:this.successSinc,
                failure: this.conexionFailure,
                timeout:this.timeout,
                scope:this
            });  
            
            
        }, 
       
       successSinc:function(resp){
            
            Phx.CP.loadingHide();
            var reg = Ext.util.JSON.decode(Ext.util.Format.trim(resp.responseText));
            if(!reg.ROOT.error){
                
              
               if (reg.ROOT.datos.operacion=='preguntar_todo'){
                   if(reg.ROOT.datos.num_estados==1 && reg.ROOT.datos.num_funcionarios==1){
                       //directamente mandamos los datos
                       Phx.CP.loadingShow();
                       var d= this.sm.getSelected().data;
                       Ext.Ajax.request({
                        // form:this.form.getForm().getEl(),
                        url:'../../sis_almacenes/control/Movimiento/siguienteEstadoMovimiento',
                        params:{id_movimiento:d.id_movimiento,
                            operacion:'cambiar',
                            id_tipo_estado:reg.ROOT.datos.id_tipo_estado,
                            id_funcionario:reg.ROOT.datos.id_funcionario_estado,
                            id_depto:reg.ROOT.datos.id_depto_estado,
                            //id_solicitud:d.id_solicitud,
                            obs:this.cmpObs.getValue(),
                            instruc_rpc:this.cmbIntrucRPC.getValue()
                            },
                        success:this.successSinc,
                        failure: this.conexionFailure,
                        timeout:this.timeout,
                        scope:this
                    }); 
                 }
                   else{
                     this.cmbTipoEstado.store.baseParams.estados= reg.ROOT.datos.estados;
                     this.cmbTipoEstado.modificado=true;
                 
                     console.log(resp)
                      if(resp.argument.data.estado=='vbrpc'){
                        this.cmbIntrucRPC.show();
                        this.cmbIntrucRPC.enable();
                     }
                     else{
                         this.cmbIntrucRPC.hide();
                         this.cmbIntrucRPC.disable(); 
                         
                     }
                     
                     this.cmpObs.setValue('');
                     this.cmbFuncionarioWf.disable();
                     this.wEstado.buttons[1].hide();
                     this.wEstado.buttons[0].show();
                     this.wEstado.show();  
                  }
                   
               }
               
                if (reg.ROOT.datos.operacion=='cambio_exitoso'){
                
                  this.reload();
                  this.wEstado.hide();
                
                }
               
                
            }else{
                
                alert('ocurrio un error durante el proceso')
            }
           
            
        },
     
  preparaMenu:function(n){
      var data = this.getSelectedData();
      var tb =this.tbar;
      //Phx.vista.MovimientoVb.superclass.preparaMenu.call(this,n);
      	   
        if(data.estado =='aprobado' ){ 
            this.getBoton('ant_estado').enable();
            this.getBoton('sig_estado').disable();
            this.getBoton('ini_estado').enable();
        }
        if(data.estado =='proceso'){
            this.getBoton('ant_estado').disable();
            this.getBoton('sig_estado').disable();
            this.getBoton('ini_estado').disable();
        }
        
        if(data.estado !='aprobado' && data.estado !='proceso' ){
            this.getBoton('ant_estado').enable();
            this.getBoton('sig_estado').enable();
            this.getBoton('ini_estado').enable();
        }
       
       
       
        return tb 
     }, 
     liberaMenu:function(){
        var tb = Phx.vista.MovimientoVb.superclass.liberaMenu.call(this);
        if(tb){
            this.getBoton('sig_estado').disable();
            this.getBoton('ini_estado').disable();
            this.getBoton('ant_estado').disable();
           
        }
        return tb
    },    
       
	
	south:
          { 
          url:'../../../sis_almacenes/vista/movimientoDetalle/MovimientoDetalleVb.php',
          title:'Detalle', 
          height:'50%',
          cls:'MovimientoDetalleVb'
         }
};
</script>
