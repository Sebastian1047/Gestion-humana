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
  // Catálogo abierto: el plan se compone solo de prendas seleccionadas.
  var PLAN_EJEMPLO={camisa:{cantidad:1,talla:'M'},pantalon:{cantidad:1,talla:'32'},botas:{cantidad:1,talla:'39'}};
  var NOMBRE_PERIODO={1:'Enero – Abril',2:'Mayo – Agosto',3:'Septiembre – Diciembre'};
  var ENTREGA_MES={1:'abril',2:'agosto',3:'diciembre'};
  var registros=leer();
  var firmaTrabajador=null, firmaEncargado=null;
  var timeoutBusqueda=null;
  var hoy=$('fecha_entrega');
  var periodoActivo=null;     // Solo se muestra el detalle del periodo elegido.
  var modoRegistro=null;      // null=consulta, normal=entrega, adelanto=adelanto.
  var anioConsulta=$('ndAnioConsulta');
  anioConsulta.value=(fechaValida(hoy.value)||{anio:new Date().getFullYear()}).anio;

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
  function planBase() {return {};}
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
    return {previsto:previsto,entregado:entregado,cuotasCubiertas:previsto>0&&entregado===previsto};
  }
  function dotacionCerrada(r) {
    var ms=movimientos(r);
    if(ms.some(function(m){return m.tipo==='normal'||(m.tipo==='adelanto'&&m.modalidad==='completo');}))return true;
    // Compatibilidad con movimientos de demostración anteriores.
    if(ms.length&&ms.every(function(m){return m.tipo==='adelanto'&&!m.modalidad;})) {
      return progreso(r.codigo,r).cuotasCubiertas;
    }
    return false;
  }
  function estado(codigo,anio,periodo,fecha) {
    var r=registros[clave(codigo,anio,periodo)],ms=movimientos(r);
    if(!ms.length)return {nombre:'Pendiente',clase:'',detalle:'Sin entregas registradas'};
    var p=progreso(codigo,r),soloAdelanto=ms.every(function(m){return m.tipo==='adelanto';});
    var inicio=anio*12+(periodo-1)*4+1,fechaEnMes=fecha.anio*12+fecha.mes;
    var resumen=p.entregado+' de '+p.previsto+' unidades programadas entregadas';
    if(dotacionCerrada(r))return {nombre:soloAdelanto&&fechaEnMes<inicio?'Adelantado completamente':
      soloAdelanto?'Completado por adelanto':'Completado',clase:'completo',detalle:resumen};
    return {nombre:soloAdelanto&&fechaEnMes<inicio?'Adelantado parcialmente':'Pendiente de completar',
      clase:'parcial',detalle:resumen+' · Falta entrega formal'};
  }
  function contexto() {
    var codigo=$('codigo').value.trim(),fecha=fechaValida(hoy.value);
    var anio=Number(anioConsulta.value);
    if(!codigo||!fecha||!periodoActivo||!Number.isInteger(anio)||anio<2020||anio>2100)return null;
    return {codigo:codigo,fecha:fecha,tipo:modoRegistro||'normal',anio:anio,periodo:periodoActivo,
      id:clave(codigo,anio,periodoActivo)};
  }
  function motivoBloqueo(ctx,r) {
    if(!ctx)return 'Selecciona trabajador, periodo y fecha válida para continuar.';
    var ms=movimientos(r);
    if(ctx.tipo==='normal' && (ctx.fecha.anio!==ctx.anio||ctx.fecha.mes!==ctx.periodo*4))
      return 'La entrega normal del periodo '+ctx.periodo+' de '+ctx.anio+' se registra en '+ENTREGA_MES[ctx.periodo]+' de '+ctx.anio+'. Cambia la fecha de entrega para habilitarla.';
    if(ctx.tipo==='adelanto'){
      var destino=siguiente(ctx.fecha);
      if(destino.anio!==ctx.anio||destino.periodo!==ctx.periodo)
        return 'El adelanto de este periodo se registra en el cuatrimestre anterior. Cambia la fecha del movimiento a ese cuatrimestre.';
    }
    if(ms.some(function(m){return m.tipo===ctx.tipo;}))
      return ctx.tipo==='normal'?'Ya existe una entrega normal para este periodo. No se permite otra.':'Ya existe un adelanto para este periodo. No se permite otro.';
    if(dotacionCerrada(r))return 'La dotación del periodo ya está cerrada. No se permite otra entrega ni adelanto.';
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
  // Los canvas están ocultos al entrar: inicializarlos solamente al abrir el formulario.
  // Inicializarlos cuando display:none provoca dimensiones cero y rompe el trazo.
  var sigTrabajador=null, sigEncargado=null;
  function asegurarFirmas() {
    if(sigTrabajador)return;
    sigTrabajador=initFirmaCanvas({
      canvas:'canvas_trabajador',hidden:'firma_trabajador_data',wrap:'wrap_trabajador',
      btnLimpiar:'limpiar_trabajador',estado:'estado_trabajador',claseActivo:'activo',
      capturaEnSuelta:true,dpiAware:false,formato:'image/jpeg',calidad:.6,
      onCambio:function(v){firmaTrabajador=v;}
    });
    sigEncargado=initFirmaCanvas({
      canvas:'canvas_encargado',hidden:'firma_encargado_data',wrap:'wrap_encargado',
      btnLimpiar:'limpiar_encargado',estado:'estado_encargado',claseActivo:'activo',
      capturaEnSuelta:true,dpiAware:false,formato:'image/jpeg',calidad:.6,
      onCambio:function(v){firmaEncargado=v;}
    });
  }
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
    var programada=document.createElement('div');
    programada.className='nd-cantidad-wrap nd-meta-wrap';
    var etiqueta=document.createElement('label');
    etiqueta.textContent='Total previsto';
    var total=document.createElement('input');
    total.type='number';total.min='1';total.max='99';total.value='1';
    total.className='nd-meta';total.id='nd_meta_'+id;
    etiqueta.appendChild(total);programada.appendChild(etiqueta);bloque.appendChild(programada);
    var nota=document.createElement('div');nota.className='nd-item-estado';nota.id='nd_estado_'+id;bloque.appendChild(nota);
    checkbox.addEventListener('change',function(){
      select.disabled=!this.checked||this.disabled;
      if(!this.checked)select.value='';
      if(sexo){sexo.disabled=!this.checked||this.disabled;if(!this.checked)sexo.checked=false;}
      input.disabled=!this.checked||this.disabled;
      var r=contexto(),p=r&&planPeriodo(r.codigo,registros[r.id])[id];
      total.disabled=!this.checked||this.disabled||!!p;
      bloque.classList.toggle('activa',this.checked);
      actualizarCobertura();
    });
    total.addEventListener('input',function(){
      var r=contexto(),p=r&&planPeriodo(r.codigo,registros[r.id])[id],d=entregadoPorArticulo(r&&registros[r.id]);
      var max=p?Math.max(0,Number(p.cantidad)-(d[id]||0)):Number(total.value);
      if(Number.isInteger(max)&&max>0){
        input.max=String(max);
        if(Number(input.value)>max)input.value=String(max);
      }
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
    var info=datosEstado(ctx),plan=info.plan,dado=info.dado,yaRecibidas=0;
    ARTICULOS.forEach(function(id){
      var checkbox=$('check_'+id),select=$(id+'_talla'),sexo=$('sexo_opuesto_'+id),
        bloque=checkbox.closest('.prenda-bloque');
      var cant=$('nd_cantidad_'+id),meta=$('nd_meta_'+id),nota=$('nd_estado_'+id),p=plan[id];
      var previsto=p?Number(p.cantidad)||0:0,dados=dado[id]||0;
      var restante=p?Math.max(0,previsto-dados):1;
      var completo=!!p&&previsto>0&&dados>=previsto;
      var habilitado=!!ctx&&!razon&&!completo;
      if(completo)yaRecibidas++;
      checkbox.disabled=!habilitado;
      // En entrega normal preseleccionar solo prendas con unidades pendientes
      // que ya estaban programadas en un adelanto; otras son opcionales.
      checkbox.checked=habilitado&&!!p&&restante>0&&ctx.tipo==='normal';
      select.disabled=!checkbox.checked;
      sexo.disabled=!checkbox.checked;sexo.checked=false;
      cant.disabled=!checkbox.checked;
      cant.max=String(restante);
      cant.value=String(ctx&&ctx.tipo==='normal'&&p?restante:1);
      meta.value=String(p?previsto:1);
      meta.disabled=!checkbox.checked||!!p;
      if(checkbox.checked&&p){
        var opt=Array.prototype.find.call(select.options,function(o){return o.value===p.talla;});
        select.value=opt?p.talla:'';
      }else select.value='';
      bloque.classList.toggle('activa',checkbox.checked);
      bloque.classList.toggle('nd-no-programada',false);
      bloque.classList.toggle('nd-ya-entregada',completo);
      nota.textContent=!ctx?'Selecciona un colaborador':
        completo?'✓ Ya entregadas '+dados+' de '+previsto+' · bloqueada':
        p?'Recibidas '+dados+' · faltan '+restante+' de '+previsto:
        'Disponible para seleccionar';
    });
    $('ndPlanDescripcion').textContent=ctx?
      'Puedes seleccionar cualquiera de las 10 prendas, con talla, cantidad a entregar y total previsto. Si adelantas menos de lo previsto, la diferencia quedará pendiente. '+yaRecibidas+
      ' prendas ya completadas están bloqueadas.':
      'Selecciona un colaborador para elegir libremente las prendas que necesita.';
    $('btn-guardar').disabled=!!razon||!ctx;
    $('btn-guardar').textContent=ctx&&ctx.tipo==='adelanto'?'✓ Registrar adelanto DEMO':'✓ Registrar entrega DEMO';
    actualizarCobertura();
  }
  function actualizarCobertura(){
    var ctx=contexto();
    if(!ctx)return;
    var seleccion=0;
    ARTICULOS.forEach(function(id){if($('check_'+id).checked)seleccion+=Number($('nd_cantidad_'+id).value)||0;});
    var modalidad=ctx.tipo==='adelanto'?
      (form.querySelector('input[name="nd_modalidad"]:checked')||{}).value:'';
    var nota=ctx.tipo==='adelanto'?
      ('Adelanto '+(modalidad==='completo'?'completo (cierra el periodo)':'parcial (permite completar después)')):
      'Entrega normal (cierra el periodo con los artículos seleccionados y pendientes)';
    var bloque=$('ndAviso'),texto=bloque.getAttribute('data-aviso')||'',clase=bloque.getAttribute('data-clase')||'';
    bloque.className='nd-aviso '+clase;
    bloque.textContent=(texto?texto+' ':'')+'Modalidad: '+nota+'. Unidades seleccionadas: '+seleccion+'.';
  }
  function renderResumen(ctx) {
    var f=fechaValida(hoy.value),anio=Number(anioConsulta.value);
    if(!Number.isInteger(anio)||anio<2020||anio>2100){$('ndResumen').innerHTML='<p class="nd-vacio">Indica un año entre 2020 y 2100.</p>';return;}
    $('ndResumen').innerHTML=[1,2,3].map(function(p){
      var e=$('codigo').value && f?estado($('codigo').value,anio,p,f):
        {nombre:'Por consultar',clase:'',detalle:'Selecciona al colaborador'};
      var r=registros[clave($('codigo').value,anio,p)];
      var n=movimientos(r).length;
      return '<button type="button" class="nd-periodo-boton'+(periodoActivo===p?' activo':'')+
        '" data-periodo="'+p+'" aria-pressed="'+(periodoActivo===p?'true':'false')+'">'+
        '<span class="nd-periodo-num">Periodo '+p+' · '+anio+'</span>'+
        '<span class="nd-periodo-meses">'+NOMBRE_PERIODO[p]+' · Entrega: '+ENTREGA_MES[p]+'</span>'+
        '<span class="cc-estado '+e.clase+'">'+escapeHtml(e.nombre)+'</span>'+
        '<span class="nd-periodo-contador">'+n+' movimiento'+(n===1?'':'s')+
        ' · '+escapeHtml(e.detalle)+'</span>'+
        '<span class="nd-periodo-abrir">'+(periodoActivo===p?'Periodo seleccionado':'Ver detalle y registrar')+' ›</span></button>';
    }).join('');
  }
  function pintarHistorial(ctx) {
    if(!ctx){$('ndHistorial').innerHTML='<p class="nd-vacio">Selecciona un colaborador y periodo para consultar.</p>';return;}
    var r=registros[ctx.id],e=estado(ctx.codigo,ctx.anio,ctx.periodo,ctx.fecha),
      plan=planPeriodo(ctx.codigo,r),dado=entregadoPorArticulo(r);
    var pendientes=[],recibidas=[];
    Object.keys(plan).forEach(function(id){
      var previsto=Number(plan[id].cantidad)||0,recibido=Math.min(previsto,dado[id]||0),resto=previsto-recibido;
      if(recibido)recibidas.push('<li>'+escapeHtml(CATALOGO[id]||id)+' · '+recibido+' / '+previsto+' · talla '+escapeHtml(plan[id].talla||'—')+'</li>');
      if(resto)pendientes.push('<li>'+escapeHtml(CATALOGO[id]||id)+' · '+resto+' pendiente'+(resto===1?'':'s')+' · talla '+escapeHtml(plan[id].talla||'—')+'</li>');
    });
    var movs=movimientos(r).map(function(m,i){
      var items=(m.articulos||[]).map(function(a){
        return '<li>'+escapeHtml(CATALOGO[a.id]||a.id)+' · talla '+escapeHtml(a.talla||'—')+
        ' · cantidad '+escapeHtml(a.cantidad)+'</li>';
      }).join('');
      var firma=m.firmaTrabajador&&/^data:image\/(jpeg|png);base64,[A-Za-z0-9+/=]+$/.test(m.firmaTrabajador)
        ? '<details><summary>Ver firma de quien recibe</summary><img alt="Firma de quien recibe" src="'+m.firmaTrabajador+'"></details>'
        : '<small>Ejemplo sin firma disponible</small>';
      return '<div class="nd-mov"><strong>Movimiento '+(i+1)+' · '+(m.tipo==='adelanto'?'Adelanto':'Entrega normal')+
        ' · '+formatoFecha(m.fecha)+'</strong><p>'+escapeHtml(m.concepto||'Sin concepto')+'</p>'+
        '<ul>'+items+'</ul>'+firma+'</div>';
    }).join('');
    $('ndHistorial').innerHTML=
      '<article class="nd-periodo-historial">'+
      '<h4>Registro principal · Periodo '+ctx.periodo+' de '+ctx.anio+'</h4>'+
      '<span class="cc-estado '+e.clase+'">'+escapeHtml(e.nombre)+'</span>'+
      '<p class="nd-subdato">'+escapeHtml(e.detalle)+'</p>'+
      '<div class="nd-periodo-balance">'+
        '<div class="nd-balance-bloque"><strong>✅ Ya entregado</strong>'+(recibidas.length?'<ul>'+recibidas.join('')+'</ul>':'<p class="nd-sin-items">Todavía no hay prendas recibidas.</p>')+'</div>'+
        '<div class="nd-balance-bloque"><strong>📋 Pendiente de entrega</strong>'+(pendientes.length?'<ul>'+pendientes.join('')+'</ul>':'<p class="nd-sin-items">No hay prendas pendientes.</p>')+'</div>'+
      '</div>'+
      '<h4>Historial de movimientos ('+movimientos(r).length+')</h4>'+
      (movs||'<p class="nd-subdato">No se han registrado adelantos ni entregas de este periodo.</p>')+
      '</article>';
  }
  // En la DEMO, las fechas se acomodan automáticamente a la operación
  // seleccionada. La validación de periodos/adelantos y duplicados permanece.
  function fechaPrueba(ctx, tipo) {
    if(tipo==='normal') {
      if(ctx.fecha.anio===ctx.anio&&ctx.fecha.mes===ctx.periodo*4)return ctx.fecha;
      return fechaValida(ctx.anio+'-'+String(ctx.periodo*4).padStart(2,'0')+'-15');
    }
    var actual=siguiente(ctx.fecha);
    if(actual.anio===ctx.anio&&actual.periodo===ctx.periodo)return ctx.fecha;
    var previo=ctx.periodo===1?{anio:ctx.anio-1,mes:12}:
      {anio:ctx.anio,mes:(ctx.periodo-1)*4};
    return fechaValida(previo.anio+'-'+String(previo.mes).padStart(2,'0')+'-15');
  }
  function contextoPrueba(ctx,tipo){
    return Object.assign({},ctx,{tipo:tipo,fecha:fechaPrueba(ctx,tipo)});
  }
  function mensajeRestriccion(ctx) {
    var r=registros[ctx.id];
    // La elegibilidad se calcula sobre la fecha simulada de cada acción,
    // y NO sobre la fecha elegida previamente para otro movimiento.
    var normalCtx=contextoPrueba(ctx,'normal');
    var adelantoCtx=contextoPrueba(ctx,'adelanto');
    var normal=motivoBloqueo(normalCtx,r);
    var adelanto=motivoBloqueo(adelantoCtx,r);
    $('ndAccionNormal').disabled=!!normal;
    $('ndAccionAdelanto').disabled=!!adelanto;
    $('ndAccionNormal').textContent='Registrar entrega normal';
    $('ndAccionAdelanto').textContent='Registrar adelanto';
    $('ndRequisitosAcciones').textContent=
      'MODO PRUEBA: puedes registrar en cualquiera de los tres periodos. '+
      'Entrega normal: fecha simulada '+formatoFecha(normalCtx.fecha.fecha)+
      '. Adelanto: fecha simulada '+formatoFecha(adelantoCtx.fecha.fecha)+'. '+
      'Al pulsar una acción, la fecha se ajusta automáticamente. '+
      (normal?'Entrega normal no disponible: '+normal+' ':'')+
      (adelanto?'Adelanto no disponible: '+adelanto:'')+
      (!normal&&!adelanto?'Solo se bloquean periodos ya cubiertos o movimientos duplicados.':'');
  }
  function abrirMovimiento(tipo) {
    var ctx=contexto();if(!ctx)return;
    var prueba=contextoPrueba(ctx,tipo);
    var razon=motivoBloqueo(prueba,registros[ctx.id]);
    if(razon){mostrarResultado(razon,true);return;}
    // Únicamente en la DEMO se precarga la fecha correcta.
    hoy.value=prueba.fecha.fecha;
    modoRegistro=tipo;
    form.querySelector('input[name="nd_tipo"][value="normal"]').checked=tipo==='normal';
    form.querySelector('input[name="nd_tipo"][value="adelanto"]').checked=tipo==='adelanto';
    render(true);
    asegurarFirmas();
    $('ndMovimientoPanel').scrollIntoView({behavior:'smooth',block:'start'});
  }
  function cerrarMovimiento() {
    modoRegistro=null;
    render(true);
  }
  function render(limpiar) {
    var ctx=contexto();
    renderResumen(ctx);
    $('ndHistorialPanel').hidden=!ctx;
    $('ndMovimientoPanel').hidden=!ctx||!modoRegistro;
    $('ndPrendasSeccion').hidden=!ctx||!modoRegistro;
    if(ctx) {
      var r=registros[ctx.id];
      $('ndPeriodoDescripcion').textContent=NOMBRE_PERIODO[ctx.periodo]+' de '+ctx.anio+
        ' · Entrega ordinaria: '+ENTREGA_MES[ctx.periodo]+'.';
      pintarHistorial(ctx);
      mensajeRestriccion(ctx);
      if(modoRegistro){
        var c=Object.assign({},ctx,{tipo:modoRegistro}),razon=motivoBloqueo(c,r);
        $('ndDestino').innerHTML='<strong>'+(modoRegistro==='adelanto'?'Adelanto':'Entrega normal')+
          ' del periodo '+ctx.periodo+' de '+ctx.anio+'</strong><br>'+NOMBRE_PERIODO[ctx.periodo]+
          ' · Fecha simulada del movimiento: '+formatoFecha(ctx.fecha.fecha)+'.';
        var aviso=$('ndAviso');
        aviso.setAttribute('data-aviso',razon||(
          modoRegistro==='adelanto'
            ? 'El adelanto del cuatrimestre anterior se descontará de esta dotación.'
            : 'Solo se registrarán las prendas pendientes del mismo periodo.'));
        aviso.setAttribute('data-clase',razon?'bloqueo':modoRegistro==='adelanto'?'adelanto':'');
        $('ndEncargadoBloque').style.display=modoRegistro==='adelanto'?'none':'';
        $('ndTextoFirmas').textContent=modoRegistro==='adelanto'
          ? 'Adelanto: firma de quien recibe. No se pide una segunda confirmación.'
          : 'Entrega formal: firma del trabajador y del encargado, una única vez.';
        pintarPrendas(c,razon);
      }
    } else if(periodoActivo && !$('codigo').value) {
      $('ndHistorialPanel').hidden=false;
      $('ndHistorial').innerHTML='<p class="nd-vacio">Selecciona primero un colaborador para ver sus movimientos.</p>';
      $('ndPeriodoDescripcion').textContent='Periodo '+periodoActivo+' · '+anioConsulta.value;
      $('ndAccionesPeriodo').hidden=true;
    }
    if(ctx)$('ndAccionesPeriodo').hidden=false;
    if(!periodoActivo)$('ndAccionesPeriodo').hidden=true;
    if(limpiar) {limpiarFirmas();limpiarResultado();}
  }
  $('ndResumen').addEventListener('click',function(e){
    var target=e.target.closest('[data-periodo]');
    if(!target||!this.contains(target))return;
    periodoActivo=Number(target.getAttribute('data-periodo'));
    modoRegistro=null;
    render(true);
  });
  anioConsulta.addEventListener('change',function(){
    modoRegistro=null;
    render(true);
  });
  $('ndCerrarPeriodo').addEventListener('click',function(){
    periodoActivo=null;modoRegistro=null;render(true);
  });
  $('ndCancelarMovimiento').addEventListener('click',cerrarMovimiento);
  $('ndAccionNormal').addEventListener('click',function(){abrirMovimiento('normal');});
  $('ndAccionAdelanto').addEventListener('click',function(){abrirMovimiento('adelanto');});
  hoy.addEventListener('change',function(){
    if(!periodoActivo){var f=fechaValida(hoy.value);if(f)anioConsulta.value=f.anio;}
    modoRegistro=null;
    render(true);
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
      modoRegistro=null; // La entrega termina y vuelve al detalle del periodo.
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
    anioConsulta.value='2027';
    periodoActivo=2;
    modoRegistro=null;
    $('ndConcepto').value='Completar dotación de agosto después del adelanto de abril';
    form.querySelector('input[name="nd_tipo"][value="normal"]').checked=true;
    form.querySelector('input[name="nd_tipo"][value="adelanto"]').checked=false;
    render(true);
    $('ndConcepto').value='Completar dotación de agosto después del adelanto de abril';
    mostrarResultado('Ejemplo cargado: abriste el periodo 2 de 2027. Verás las botas adelantadas en abril y el camibuso y pantalón pendientes. Para completarlos, pulsa «Registrar entrega normal».',false);
  });
  $('ndLimpiarDemo').addEventListener('click',function(){
    if(!window.confirm('¿Borrar TODOS los movimientos de prueba de esta DEMO guardados en este navegador?'))return;
    registros={};
    try{localStorage.removeItem(STORAGE_KEY);}catch(_){}
    render(true);
    mostrarResultado('Se eliminaron los movimientos ficticios. No se afectó la base de datos.',false);
  });
  // Primero se consultan los periodos. El formulario solo aparece
  // al pulsar una acción explícita dentro del periodo seleccionado.
  render(false);
});
