/* Nueva Dotación integrada (solo DEMO).
 * Usa el mismo registro principal e historial de ControlCuatrimestral.js.
 * NO efectúa POST a la API; el entorno real sigue aislado y sin escrituras.
 */
document.addEventListener('DOMContentLoaded', function () {
  'use strict';
  var $ = function (id) { return document.getElementById(id); };
  var form = $('form-dotacion');
  if (!form) return;

  var STORAGE_KEY = 'gestion_humana_demo_cuatrimestres_v1';
  var CATALOGO = {
    camisa:'Camibuso', camisa_oxford:'Camisa Oxford', camisa_polo:'Camisa Polo',
    pantalon:'Pantalón', sudadera:'Sudadera', jean:'Jean', overol:'Overol',
    zapatos_seguridad:'Zapatos de seguridad', zapatos:'Zapatos', botas:'Botas'
  };
  var ARTICULOS = Object.keys(CATALOGO);
  // Planes por empleado estrictamente ficticios, iguales al simulador anterior.
  var PLANES = {
    DEMO001:{camisa:{cantidad:1,talla:'M'},pantalon:{cantidad:1,talla:'32'},botas:{cantidad:1,talla:'39'}},
    DEMO002:{camisa_polo:{cantidad:1,talla:'M'},jean:{cantidad:1,talla:'32'},zapatos_seguridad:{cantidad:1,talla:'39'}},
    DEMO003:{camisa:{cantidad:1,talla:'L'},overol:{cantidad:1,talla:'L'},zapatos_seguridad:{cantidad:1,talla:'40'}}
  };
  var NOMBRE_PERIODO={1:'Enero – Abril',2:'Mayo – Agosto',3:'Septiembre – Diciembre'};
  var ENTREGA_MES={1:'abril',2:'agosto',3:'diciembre'};
  var registros=leer();
  var firmaTrabajador=null, firmaEncargado=null;
  var timeoutBusqueda=null;
  var hoy=$('fecha_entrega');

  function escapeHtml(str) {
    return String(str==null?'':str).replace(/[&<>"']/g,function(x){
      return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[x];
    });
  }
  function fechaValida(texto) {
    var m=/^(\d{4})-(\d{2})-(\d{2})$/.exec(String(texto||''));
    if(!m)return null;
    var y=+m[1],mes=+m[2],dia=+m[3],d=new Date(y,mes-1,dia);
    if(d.getFullYear()!==y||d.getMonth()!==mes-1||d.getDate()!==dia)return null;
    return {anio:y,mes:mes,dia:dia,periodo:Math.ceil(mes/4),fecha:texto};
  }
  function formatoFecha(texto) {
    var f=fechaValida(texto);return f?String(f.dia).padStart(2,'0')+'/'+String(f.mes).padStart(2,'0')+'/'+f.anio:escapeHtml(texto);
  }
  function clave(codigo,anio,periodo){return codigo+'|'+anio+'|'+periodo;}
  function siguiente(info) {return info.periodo===3?{anio:info.anio+1,periodo:1}:{anio:info.anio,periodo:info.periodo+1};}
  function planBase(codigo) {return JSON.parse(JSON.stringify(PLANES[codigo]||PLANES.DEMO001));}
  function leer() {
    try {
      var r=JSON.parse(localStorage.getItem(STORAGE_KEY)||'{}');
      return r&&typeof r==='object'&&!Array.isArray(r)?r:{};
    } catch(_){return {};}
  }
  function guardarLocal() {
    try {localStorage.setItem(STORAGE_KEY,JSON.stringify(registros));return true;}catch(_){return false;}
  }
  function movimientos(r){return r&&Array.isArray(r.movimientos)?r.movimientos:[];}
  function planPeriodo(codigo,r){return r&&r.plan?r.plan:planBase(codigo);}
  function entregadoPorArticulo(r) {
    var totales={};
    movimientos(r).forEach(function(m) {
      (m.articulos||[]).forEach(function(a){totales[a.id]=(totales[a.id]||0)+Number(a.cantidad||0);});
    });
    return totales;
  }
  function progreso(codigo,r) {
    var plan=planPeriodo(codigo,r),dado=entregadoPorArticulo(r),previsto=0,entregado=0;
    Object.keys(plan).forEach(function(id){
      previsto+=Number(plan[id].cantidad)||0;
      entregado+=Math.min(Number(plan[id].cantidad)||0,dado[id]||0);
    });
    return {previsto:previsto,entregado:entregado,completo:previsto>0&&entregado===previsto};
  }
  function estado(codigo,anio,periodo,fecha) {
    var r=registros[clave(codigo,anio,periodo)],ms=movimientos(r);
    if(!ms.length)return {nombre:'Pendiente',clase:'',detalle:'Sin entregas registradas'};
    var p=progreso(codigo,r),soloAdelanto=ms.every(function(m){return m.tipo==='adelanto';});
    var inicio=anio*12+(periodo-1)*4+1;
    var fechaEnMes=fecha.anio*12+fecha.mes;
    if(p.completo)return {nombre:soloAdelanto&&fechaEnMes<inicio?'Adelantado completamente':soloAdelanto?'Completado por adelanto':'Completado',clase:'completo',detalle:p.entregado+' de '+p.previsto+' artículos entregados'};
    return {nombre:soloAdelanto&&fechaEnMes<inicio?'Adelantado parcialmente':'Pendiente de completar',clase:'parcial',detalle:p.entregado+' de '+p.previsto+' artículos entregados'};
  }
  function contexto() {
    var codigo=$('codigo').value.trim(),fecha=fechaValida(hoy.value),seleccion=form.querySelector('input[name="nd_tipo"]:checked');
    if(!codigo||!fecha||!seleccion)return null;
    var tipo=seleccion.value,destino=tipo==='adelanto'?siguiente(fecha):{anio:fecha.anio,periodo:fecha.periodo};
    return {codigo:codigo,fecha:fecha,tipo:tipo,anio:destino.anio,periodo:destino.periodo,id:clave(codigo,destino.anio,destino.periodo)};
  }
  function motivoBloqueo(ctx,r) {
    if(!ctx) return 'Selecciona un trabajador y una fecha válida para continuar.';
    var ms=movimientos(r);
    if(ctx.tipo==='normal' && ctx.fecha.mes!==ctx.periodo*4)
      return 'Las entregas normales del periodo '+ctx.periodo+' se realizan en '+ENTREGA_MES[ctx.periodo]+'. Puedes cambiar la fecha para probar ese mes.';
    if(ms.some(function(m){return m.tipo===ctx.tipo;}))
      return ctx.tipo==='normal'?'Ya existe una entrega normal para este periodo. No se permite otra.':'Ya existe un adelanto para este periodo. No se permite otro.';
    if(progreso(ctx.codigo,r).completo)return 'La dotación del periodo está cubierta por completo. No se permite repetir prendas ni registrar otra entrega.';
    return '';
  }

  function mostrarResultado(texto,esError){
    var el=$('mensaje-resultado');
    el.textContent=texto;
    el.className='nd-resultado '+(esError?'error':'ok');
    el.style.display='block';
    if(esError)el.scrollIntoView({behavior:'smooth',block:'nearest'});
  }
  function limpiarResultado() {
    var el=$('mensaje-resultado');
    el.textContent='';
    el.className='mensaje-oculto';
    el.style.display='none';
  }
  // Firma original de dotaciones, sin pedir nunca otras dos firmas para confirmar entrega.
  var sigTrabajador=initFirmaCanvas({
    canvas:'canvas_trabajador',hidden:'firma_trabajador_data',wrap:'wrap_trabajador',
    btnLimpiar:'limpiar_trabajador',estado:'estado_trabajador',claseActivo:'activo',
    capturaEnSuelta:true,dpiAware:false,formato:'image/jpeg',calidad:.6,
    onCambio:function(v){firmaTrabajador=v;}
  });
  var sigEncargado=initFirmaCanvas({
    canvas:'canvas_encargado',hidden:'firma_encargado_data',wrap:'wrap_encargado',
    btnLimpiar:'limpiar_encargado',estado:'estado_encargado',claseActivo:'activo',
    capturaEnSuelta:true,dpiAware:false,formato:'image/jpeg',calidad:.6,
    onCambio:function(v){firmaEncargado=v;}
  });
  function limpiarFirmas() {
    if(sigTrabajador)sigTrabajador.limpiar();
    if(sigEncargado)sigEncargado.limpiar();
    firmaTrabajador=null;firmaEncargado=null;
  }

  // Mantener el buscador original de colaboradores DEMO.
  var inputBusqueda=$('busqueda'),lista=$('sugerencias');
  inputBusqueda.addEventListener('input',function(){
    $('codigo').value='';$('nombre').value='';$('cargo').value='';
    clearTimeout(timeoutBusqueda);
    var busq=this.value.trim();
    render(false);
    if(busq.length<2){lista.style.display='none';return;}
    timeoutBusqueda=setTimeout(function(){
      fetch('../Controller/DotacionController.php?action=buscar_empleado&q='+encodeURIComponent(busq))
      .then(function(r){return r.json();})
      .then(function(data){
        lista.textContent='';
        if(!Array.isArray(data)||!data.length){
          var empty=document.createElement('div');empty.className='sugerencia-vacia';empty.textContent='No se encontraron colaboradores.';lista.appendChild(empty);
        } else data.forEach(function(emp){
          var item=document.createElement('div');item.className='sugerencia-item';
          var cod=document.createElement('span');cod.className='sugerencia-codigo';cod.textContent=emp.codigo;
          item.appendChild(cod);item.appendChild(document.createTextNode(' '+(emp.nombre||emp.nombre_empleado||'')));
          item.addEventListener('mousedown',function(e){
            e.preventDefault();
            $('codigo').value=emp.codigo||'';
            $('nombre').value=emp.nombre||emp.nombre_empleado||'';
            $('cargo').value=emp.cargo||emp.nombre_centro_costo||'';
            inputBusqueda.value=$('nombre').value;
            lista.style.display='none';lista.textContent='';
            limpiarResultado();render(true);
          });
          lista.appendChild(item);
        });
        lista.style.display='block';
      }).catch(function(){lista.style.display='none';});
    },250);
  });
  document.addEventListener('click',function(e){if(!inputBusqueda.contains(e.target)&&!lista.contains(e.target))lista.style.display='none';});
  // Cada artículo mantiene su bloque, la talla y el selector de género original.
  // Se añade solo la cantidad y el estado por periodo para la DEMO.
  ARTICULOS.forEach(function(id){
    var checkbox=$('check_'+id),select=$(id+'_talla'),bloque=checkbox.closest('.prenda-bloque');
    var sexo=$('sexo_opuesto_'+id);
    var cant=document.createElement('div');cant.className='nd-cantidad-wrap';
    var label=document.createElement('label');label.textContent='Cantidad';
    var input=document.createElement('input');input.type='number';input.min='1';input.max='1';input.value='1';
    input.className='nd-cantidad';input.id='nd_cantidad_'+id;
    label.appendChild(input);cant.appendChild(label);
    bloque.appendChild(cant);
    var nota=document.createElement('div');nota.className='nd-item-estado';nota.id='nd_estado_'+id;bloque.appendChild(nota);
    checkbox.addEventListener('change',function(){
      select.disabled=!this.checked||this.disabled;
      if(!this.checked)select.value='';
      if(sexo){sexo.disabled=!this.checked||this.disabled;if(!this.checked)sexo.checked=false;}
      input.disabled=!this.checked||this.disabled;
      bloque.classList.toggle('activa',this.checked);
      actualizarCobertura();
    });
    input.addEventListener('input',actualizarCobertura);
  });
  // Mismo criterio que el formulario original para prendas del sexo opuesto.
  var opuesto={M:'F',F:'M'};
  function generoPrenda(id) {
    var g=$('genero').value;
    return g&&$('sexo_opuesto_'+id).checked?opuesto[g]:g;
  }
  function datosEstado(ctx) {
    var base=ctx?registros[ctx.id]:null;
    return {registro:base,plan:ctx?planPeriodo(ctx.codigo,base):{},dado:entregadoPorArticulo(base)};
  }
  function pintarPrendas(ctx,razon) {
    var info=datosEstado(ctx),plan=info.plan,dado=info.dado;
    var pendientes=0,programadas=0;
    ARTICULOS.forEach(function(id){
      var checkbox=$('check_'+id),select=$(id+'_talla'),sexo=$('sexo_opuesto_'+id),bloque=checkbox.closest('.prenda-bloque');
      var cant=$('nd_cantidad_'+id),nota=$('nd_estado_'+id),p=plan[id];
      var previsto=p?Number(p.cantidad)||0:0;
      var ya=Math.min(previsto,dado[id]||0),restante=Math.max(0,previsto-ya);
      if(previsto){programadas++;if(restante)pendientes++;}
      var habilitado=!!ctx&&!razon&&restante>0;
      checkbox.disabled=!habilitado;
      checkbox.checked=habilitado&&ctx.tipo==='normal';
      select.disabled=!checkbox.checked;
      sexo.disabled=!checkbox.checked;
      sexo.checked=false;
      cant.disabled=!checkbox.checked;
      cant.max=String(restante||1);
      cant.value=String(restante||1);
      if(checkbox.checked && p){
        var opt=Array.prototype.find.call(select.options,function(o){return o.value===p.talla;});
        select.value=opt?p.talla:'';
      } else select.value='';
      bloque.classList.toggle('activa',checkbox.checked);
      bloque.classList.toggle('nd-no-programada',!p);
      bloque.classList.toggle('nd-ya-entregada',previsto>0&&restante===0);
      nota.textContent=!ctx?'Selecciona un colaborador':
        !p?'No programada (DEMO)':restante===0?'✓ Ya entregado: '+ya+' de '+previsto:
        'Pendientes: '+restante+' de '+previsto;
    });
    $('ndPlanDescripcion').textContent=ctx ?
      'Plan ficticio de '+$('nombre').value+': '+programadas+' prendas programadas, '+pendientes+
      ' con cantidades pendientes. Las prendas no programadas o ya entregadas están bloqueadas.' :
      'Selecciona un colaborador para ver las prendas que corresponden a su dotación de prueba.';
    $('btn-guardar').disabled=!!razon||!ctx;
    $('btn-guardar').textContent=ctx&&ctx.tipo==='adelanto'?'✓ Registrar adelanto DEMO':'✓ Registrar entrega DEMO';
    actualizarCobertura();
  }
  function actualizarCobertura(){
    var ctx=contexto();
    if(!ctx)return;
    var p=planPeriodo(ctx.codigo,registros[ctx.id]),d=entregadoPorArticulo(registros[ctx.id]);
    var pendienteTotal=0,seleccion=0;
    Object.keys(p).forEach(function(id){
      pendienteTotal+=Math.max(0,Number(p[id].cantidad)-(d[id]||0));
      if($('check_'+id).checked)seleccion+=Number($('nd_cantidad_'+id).value)||0;
    });
    var nota=ctx.tipo==='adelanto'?(seleccion===0?'Selecciona las prendas a adelantar':
      seleccion>=pendienteTotal?'Adelanto completo':'Adelanto parcial'):'Entrega normal (solo artículos pendientes)';
    var bloque=$('ndAviso');
    var texto=bloque.getAttribute('data-aviso')||'';
    var clase=bloque.getAttribute('data-clase')||'';
    bloque.className='nd-aviso '+clase;
    bloque.textContent=(texto?texto+' ':'')+'Modalidad: '+nota+'.';
  }
  function renderResumen(ctx){
    var fecha=ctx?ctx.fecha:fechaValida(hoy.value);
    var anio=fecha?fecha.anio:new Date().getFullYear();
    $('ndResumen').innerHTML=[1,2,3].map(function(p){
      var e=ctx?estado(ctx.codigo,anio,p,fecha):{nombre:'Por consultar',clase:'',detalle:'Selecciona un colaborador'};
      return '<div class="cc-periodo'+(fecha&&p===fecha.periodo?' actual':'')+'">'+
        '<div class="cc-periodo-topo">Periodo '+p+'</div>'+
        '<div class="cc-periodo-fechas">'+NOMBRE_PERIODO[p]+' · '+ENTREGA_MES[p]+'</div>'+
        '<span class="cc-estado '+e.clase+'">'+escapeHtml(e.nombre)+'</span>'+
        '<div class="cc-info-p">'+escapeHtml(e.detalle)+'</div></div>';
    }).join('');
  }
  function pintarHistorial(ctx){
    if(!ctx){$('ndHistorial').innerHTML='<div class="nd-vacio">Selecciona el colaborador y la fecha de la entrega.</div>';return;}
    var periodos=[1,2,3].map(function(p){return {anio:ctx.fecha.anio,periodo:p};});
    var proximo=siguiente(ctx.fecha);
    if(proximo.anio!==ctx.fecha.anio)periodos.push(proximo);
    if(ctx.anio!==ctx.fecha.anio && !periodos.some(function(p){return p.anio===ctx.anio&&p.periodo===ctx.periodo;}))periodos.push({anio:ctx.anio,periodo:ctx.periodo});
    $('ndHistorial').innerHTML=periodos.map(function(p){
      var r=registros[clave(ctx.codigo,p.anio,p.periodo)],e=estado(ctx.codigo,p.anio,p.periodo,ctx.fecha);
      var movimientosHTML=movimientos(r).map(function(m,i){
        var items=(m.articulos||[]).map(function(a){
          return '<li>'+escapeHtml(CATALOGO[a.id]||a.id)+', talla '+escapeHtml(a.talla||'—')+', cantidad '+escapeHtml(a.cantidad)+'</li>';
        }).join('');
        var firma=m.firmaTrabajador && /^data:image\/(jpeg|png);base64,[A-Za-z0-9+/=]+$/.test(m.firmaTrabajador)
          ? '<details><summary>Ver firma de quien recibe</summary><img alt="Firma" src="'+m.firmaTrabajador+'"></details>':
          '<small>Ejemplo sin firma disponible</small>';
        return '<div class="nd-mov"><strong>'+(m.tipo==='adelanto'?'Adelanto':'Entrega normal')+' · '+formatoFecha(m.fecha)+'</strong>'+
          '<p>'+escapeHtml(m.concepto||'Sin concepto')+'</p><ul>'+items+'</ul>'+firma+'</div>';
      }).join('');
      return '<article class="nd-periodo-historial"><h4>Periodo '+p.periodo+' · '+p.anio+'</h4>'+
        '<span class="cc-estado '+e.clase+'">'+escapeHtml(e.nombre)+'</span>'+
        '<p class="nd-subdato">'+escapeHtml(e.detalle)+'</p>'+
        (movimientosHTML||'<p class="nd-subdato">Sin movimientos registrados.</p>')+'</article>';
    }).join('');
  }
  function render(limpiar){
    var ctx=contexto(),r=ctx?registros[ctx.id]:null,razon=motivoBloqueo(ctx,r);
    $('ndDestino').innerHTML=ctx?'<strong>'+(ctx.tipo==='adelanto'?'Adelanto':'Entrega normal')+
      ' para el periodo '+ctx.periodo+' de '+ctx.anio+'</strong><br>'+NOMBRE_PERIODO[ctx.periodo]+
      ' · Mes de entrega: '+ENTREGA_MES[ctx.periodo]+'.<br>Fecha física: '+formatoFecha(ctx.fecha.fecha):
      'Selecciona un trabajador y una fecha para calcular la dotación.';
    var aviso=$('ndAviso');
    aviso.setAttribute('data-aviso',ctx&&razon?razon:ctx&&ctx.tipo==='adelanto'?
      'Un adelanto corresponde al siguiente cuatrimestre. Las prendas ya recibidas no se repetirán.':
      ctx?'El sistema descuenta automáticamente los artículos adelantados.':'');
    aviso.setAttribute('data-clase',razon?'bloqueo':ctx&&ctx.tipo==='adelanto'?'adelanto':'');
    $('ndEncargadoBloque').style.display=ctx&&ctx.tipo==='adelanto'?'none':'';
    $('ndTextoFirmas').textContent=ctx&&ctx.tipo==='adelanto'
      ? 'Adelanto: se solicita solo la firma del trabajador que recibe. La entrega formal solicita las dos firmas originales.'
      : 'Entrega normal: el trabajador y el encargado firman esta única entrega, sin confirmación duplicada.';
    renderResumen(ctx);
    pintarHistorial(ctx);
    pintarPrendas(ctx,razon);
    if(limpiar){limpiarFirmas();limpiarResultado();}
  }
  $('fecha_entrega').addEventListener('change',function(){render(true);});
  Array.prototype.forEach.call(form.querySelectorAll('input[name="nd_tipo"]'),function(radio){
    radio.addEventListener('change',function(){render(true);});
  });

  function recogerArticulos(ctx,r){
    var plan=planPeriodo(ctx.codigo,r),entregados=entregadoPorArticulo(r);
    var elegidos=[],cantidadTotal=0,pendienteTotal=0;
    Object.keys(plan).forEach(function(id){
      var max=Number(plan[id].cantidad)-(entregados[id]||0);
      if(max<=0)return;
      pendienteTotal+=max;
      var chk=$('check_'+id);
      if(!chk.checked)return;
      if(chk.disabled)throw Error('La prenda '+CATALOGO[id]+' está bloqueada.');
      var cantidad=Number($('nd_cantidad_'+id).value),talla=$(id+'_talla').value;
      if(!Number.isInteger(cantidad)||cantidad<1||cantidad>max)
        throw Error('La cantidad de '+CATALOGO[id]+' no puede superar lo pendiente ('+max+').');
      if(!talla)throw Error('Selecciona una talla para '+CATALOGO[id]+'.');
      elegidos.push({id:id,cantidad:cantidad,talla:talla,genero:generoPrenda(id)});
      cantidadTotal+=cantidad;
    });
    if(!elegidos.length)throw Error('Selecciona al menos una prenda pendiente.');
    if(ctx.tipo==='normal'&&cantidadTotal!==pendienteTotal)
      throw Error('La entrega normal debe completar todas las prendas pendientes del periodo. Selecciona los artículos faltantes.');
    return elegidos;
  }
  form.addEventListener('submit',function(e){
    e.preventDefault();
    var ctx=contexto();
    try{
      if(!ctx)throw Error('Selecciona un trabajador con el buscador y una fecha válida.');
      var genero=$('genero').value;
      if(genero!=='M'&&genero!=='F')throw Error('Selecciona el género del trabajador.');
      var razon=motivoBloqueo(ctx,registros[ctx.id]);
      if(razon)throw Error(razon);
      var concepto=$('ndConcepto').value.trim();
      if(!concepto)throw Error('Escribe el concepto de la entrega o adelanto.');
      if(!firmaTrabajador)throw Error('El colaborador debe firmar que recibió las prendas.');
      if(ctx.tipo==='normal'&&!firmaEncargado)throw Error('La entrega normal también requiere la firma del encargado.');
      var articulos=recogerArticulos(ctx,registros[ctx.id]);
      var original=registros[ctx.id];
      var nuevo=original?JSON.parse(JSON.stringify(original)):{
        codigo:ctx.codigo,anio:ctx.anio,periodo:ctx.periodo,plan:planBase(ctx.codigo),movimientos:[]
      };
      var movimiento={
        tipo:ctx.tipo,fecha:ctx.fecha.fecha,concepto:concepto,articulos:articulos,
        firmaTrabajador:firmaTrabajador,firmaEncargado:ctx.tipo==='normal'?firmaEncargado:null,
        nombreTrabajador:$('nombre').value,centroCostos:$('cargo').value,
        genero:genero,cambioCarnet:(form.querySelector('input[name="cambio_carnet_deterioro"]:checked')||{}).value||'',
        observaciones:$('observaciones').value.trim()
      };
      nuevo.movimientos.push(movimiento);
      registros[ctx.id]=nuevo;
      if(!guardarLocal()){
        if(original)registros[ctx.id]=original;else delete registros[ctx.id];
        throw Error('No fue posible guardar el movimiento de prueba en el navegador. Comprueba el espacio disponible.');
      }
      $('ndConcepto').value='';
      $('observaciones').value='';
      render(true);
      var estadoActual=estado(ctx.codigo,ctx.anio,ctx.periodo,ctx.fecha);
      mostrarResultado('Movimiento DEMO registrado correctamente. Registro único del periodo '+ctx.periodo+'/'+ctx.anio+': '+estadoActual.nombre+'.',false);
    }catch(error){mostrarResultado(error.message||'Error al registrar el movimiento.',true);}
  });
  // Ejemplo compartido con el simulador existente, para verificar el mismo historial.
  $('ndCargarEjemplo').addEventListener('click',function(){
    var codigo='DEMO001',id1=clave(codigo,2027,1),id2=clave(codigo,2027,2);
    if((registros[id1]||registros[id2])&&!window.confirm('Este ejemplo reemplazará las dotaciones ficticias P1 y P2 de DEMO001 en 2027. ¿Continuar?'))return;
    var plan=planBase(codigo),previo1=registros[id1],previo2=registros[id2];
    registros[id1]={codigo:codigo,anio:2027,periodo:1,plan:planBase(codigo),movimientos:[{
      tipo:'normal',fecha:'2027-04-10',concepto:'Entrega de abril (caso de prueba)',
      articulos:Object.keys(plan).map(function(id){return {id:id,cantidad:plan[id].cantidad,talla:plan[id].talla};}),
      firmaTrabajador:null,firmaEncargado:null
    }]};
    registros[id2]={codigo:codigo,anio:2027,periodo:2,plan:planBase(codigo),movimientos:[{
      tipo:'adelanto',fecha:'2027-04-20',concepto:'Cambio de labor: adelanto de botas para agosto (caso de prueba)',
      articulos:[{id:'botas',cantidad:1,talla:'39'}],firmaTrabajador:null,firmaEncargado:null
    }]};
    if(!guardarLocal()){
      if(previo1)registros[id1]=previo1;else delete registros[id1];
      if(previo2)registros[id2]=previo2;else delete registros[id2];
      mostrarResultado('No se pudo guardar el ejemplo en este navegador.',true);return;
    }
    $('codigo').value=codigo;
    $('nombre').value='Persona Demo Uno';
    $('cargo').value='Area de ejemplo A';
    $('busqueda').value='Persona Demo Uno';
    $('genero').value='M';
    $('fecha_entrega').value='2027-08-15';
    $('ndConcepto').value='Completar dotación de agosto después del adelanto de abril';
    form.querySelector('input[name="nd_tipo"][value="normal"]').checked=true;
    form.querySelector('input[name="nd_tipo"][value="adelanto"]').checked=false;
    render(true);
    $('ndConcepto').value='Completar dotación de agosto después del adelanto de abril';
    mostrarResultado('Ejemplo cargado: botas adelantadas el 20 de abril de 2027; en agosto solo quedan camibuso y pantalón.',false);
  });
  $('ndLimpiarDemo').addEventListener('click',function(){
    if(!window.confirm('¿Borrar TODOS los movimientos de prueba de esta DEMO guardados en este navegador?'))return;
    registros={};
    try{localStorage.removeItem(STORAGE_KEY);}catch(_){}
    render(true);
    mostrarResultado('Se eliminaron los movimientos ficticios. No se afectó la base de datos.',false);
  });
  // Fuera de abril/agosto/diciembre, priorizar por defecto el adelanto.
  // La entrega normal sigue disponible para simularla eligiendo su mes.
  var fechaInicial=fechaValida(hoy.value);
  if(fechaInicial&&[4,8,12].indexOf(fechaInicial.mes)===-1){
    form.querySelector('input[name="nd_tipo"][value="adelanto"]').checked=true;
    form.querySelector('input[name="nd_tipo"][value="normal"]').checked=false;
  }
  render(false);
});
