/* Control cuatrimestral de Dotaciones: SIMULACIÓN exclusivamente en localStorage.
 * No utiliza endpoints de escritura ni modifica las dotaciones originales.
 * Una clave codigo|año|periodo => un solo registro principal con movimientos.
 */
(function () {
  'use strict';
  var personas = Array.isArray(window.PERSONAS_DOTACION_DEMO) ? window.PERSONAS_DOTACION_DEMO : [];
  var STORAGE_KEY = 'gestion_humana_demo_cuatrimestres_v1';
  var CATALOGO = {
    camisa: 'Camibuso', camisa_oxford: 'Camisa Oxford', camisa_polo: 'Camisa Polo',
    pantalon: 'Pantalón', sudadera: 'Sudadera', jean: 'Jean', overol: 'Overol',
    zapatos_seguridad: 'Zapatos de seguridad', zapatos: 'Zapatos', botas: 'Botas'
  };
  // Dotación ESPERADA ficticia, no la obtenida de la base real.
  var PLANES = {
    DEMO001: { camisa: { cantidad: 1, talla: 'M' }, pantalon: { cantidad: 1, talla: '32' }, botas: { cantidad: 1, talla: '39' } },
    DEMO002: { camisa_polo: { cantidad: 1, talla: 'M' }, jean: { cantidad: 1, talla: '32' }, zapatos_seguridad: { cantidad: 1, talla: '39' } },
    DEMO003: { camisa: { cantidad: 1, talla: 'L' }, overol: { cantidad: 1, talla: 'L' }, zapatos_seguridad: { cantidad: 1, talla: '40' } }
  };
  var MESES = {1:'Enero – Abril',2:'Mayo – Agosto',3:'Septiembre – Diciembre'};
  var MES_ENTREGA = {1:'abril',2:'agosto',3:'diciembre'};
  var $ = function (id) { return document.getElementById(id); };
  if (!$('ccPersona') || !personas.length) return;
  var registros = cargar();
  var firmaTrabajador = null, firmaEncargado = null;
  var firmaInstTrabajador, firmaInstEncargado;

  function cargar() {
    try {
      var raw = localStorage.getItem(STORAGE_KEY);
      var obj = raw ? JSON.parse(raw) : {};
      return obj && typeof obj === 'object' && !Array.isArray(obj) ? obj : {};
    } catch (_) { return {}; }
  }
  function persistir() {
    try { localStorage.setItem(STORAGE_KEY, JSON.stringify(registros)); return true; }
    catch (_) { return false; }
  }
  function esc(x) { return String(x == null ? '' : x).replace(/[&<>"']/g, function (ch) {
    return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[ch];
  }); }
  function fechaActualLocal() {
    var d = new Date();
    var pad = function (n) { return String(n).padStart(2,'0'); };
    return d.getFullYear() + '-' + pad(d.getMonth()+1) + '-' + pad(d.getDate());
  }
  function parseFecha(s) {
    var m = /^(\d{4})-(\d{2})-(\d{2})$/.exec(String(s||''));
    if (!m) return null;
    var y = Number(m[1]), mes = Number(m[2]), dia = Number(m[3]);
    var d = new Date(y,mes-1,dia);
    return (d.getFullYear()===y && d.getMonth()===mes-1 && d.getDate()===dia)
      ? { anio:y, mes:mes, dia:dia, periodo:Math.ceil(mes/4), fecha:s } : null;
  }
  function fechaLegible(s) {
    if (!parseFecha(s)) return esc(s);
    var a=s.split('-');return a[2]+'/'+a[1]+'/'+a[0];
  }
  function clave(codigo,anio,periodo) { return codigo+'|'+anio+'|'+periodo; }
  function siguiente(info) {
    return info.periodo===3
      ? { anio:info.anio+1, periodo:1 }
      : { anio:info.anio, periodo:info.periodo+1 };
  }
  function contexto() {
    var codigo = $('ccPersona').value, fecha = parseFecha($('ccFecha').value);
    if (!codigo || !fecha) return null;
    var tipo = document.querySelector('input[name="ccTipo"]:checked').value;
    var destino = tipo==='adelanto' ? siguiente(fecha) : {anio:fecha.anio,periodo:fecha.periodo};
    return { codigo:codigo, fecha:fecha, tipo:tipo, anio:destino.anio,
      periodo:destino.periodo, id:clave(codigo,destino.anio,destino.periodo) };
  }
  function planBase(codigo) {
    return JSON.parse(JSON.stringify(PLANES[codigo] || PLANES.DEMO001));
  }
  function registroActual(ctx) {
    return registros[ctx.id] || null;
  }
  function movimientos(r) {
    return r && Array.isArray(r.movimientos) ? r.movimientos : [];
  }
  function entregadas(r) {
    var resultado = {};
    movimientos(r).forEach(function (mov) {
      (mov.articulos||[]).forEach(function (a) {
        resultado[a.id] = (resultado[a.id]||0)+Number(a.cantidad||0);
      });
    });
    return resultado;
  }
  function planDelPeriodo(codigo, r) { return r && r.plan ? r.plan : planBase(codigo); }
  function progreso(codigo,r) {
    var plan=planDelPeriodo(codigo,r), totales=entregadas(r), entregado=0, previsto=0;
    Object.keys(plan).forEach(function (art) {
      previsto+=plan[art].cantidad;
      entregado+=Math.min(plan[art].cantidad,totales[art]||0);
    });
    return {previsto:previsto,entregado:entregado,total:previsto>0&&entregado===previsto};
  }
  function estado(codigo,anio,periodo,fecha) {
    var r=registros[clave(codigo,anio,periodo)], ms=movimientos(r);
    if (!ms.length) return {texto:'Pendiente',clase:'',descripcion:'Sin movimientos registrados'};
    var p=progreso(codigo,r);
    var soloAdelantos=ms.every(function(m){return m.tipo==='adelanto';});
    var yaInicio=fecha.anio>anio||(fecha.anio===anio&&fecha.mes>=(periodo-1)*4+1);
    if(p.total) {
      return {texto:soloAdelantos&&!yaInicio?'Adelantado completamente':soloAdelantos?'Completado por adelanto':'Completado',
        clase:'completo',descripcion:p.entregado+' de '+p.previsto+' artículos entregados'};
    }
    return {texto:soloAdelantos&&!yaInicio?'Adelantado parcialmente':'Pendiente de completar',
      clase:'parcial',descripcion:p.entregado+' de '+p.previsto+' artículos entregados'};
  }
  function seleccionDisponible(ctx,r) {
    var movs=movimientos(r);
    if(ctx.tipo==='normal' && ctx.fecha.mes!==ctx.periodo*4)
      return 'La entrega normal del periodo '+ctx.periodo+' se realiza en '+MES_ENTREGA[ctx.periodo]+'. Cambia la fecha simulada para probarla.';
    if(movs.some(function(m){return m.tipo===ctx.tipo;}))
      return ctx.tipo==='adelanto'
        ? 'Ya existe un adelanto para este periodo. No se permite un segundo adelanto.'
        : 'Ya existe la entrega normal de este periodo. No se permite una segunda entrega.';
    if(progreso(ctx.codigo,r).total)
      return 'La dotación de este periodo ya está completa. No es posible entregar más artículos.';
    return '';
  }

  personas.forEach(function (p) {
    var opt=document.createElement('option');
    opt.value=p.codigo;
    opt.textContent=p.codigo+' · '+p.nombre;
    $('ccPersona').appendChild(opt);
  });
  $('ccFecha').value=fechaActualLocal();

  firmaInstTrabajador=initFirmaCanvas({
    canvas:'ccFirmaTrabajador',wrap:'ccFirmaTrabajadorWrap',btnLimpiar:'ccLimpiarTrabajador',
    estado:'ccEstadoTrabajador',capturaEnSuelta:true,dpiAware:false,formato:'image/jpeg',calidad:0.6,
    onCambio:function(v){firmaTrabajador=v;}
  });
  firmaInstEncargado=initFirmaCanvas({
    canvas:'ccFirmaEncargado',wrap:'ccFirmaEncargadoWrap',btnLimpiar:'ccLimpiarEncargado',
    estado:'ccEstadoEncargado',capturaEnSuelta:true,dpiAware:false,formato:'image/jpeg',calidad:0.6,
    onCambio:function(v){firmaEncargado=v;}
  });

  function limpiarFirmas() {
    if(firmaInstTrabajador) firmaInstTrabajador.limpiar();
    if(firmaInstEncargado) firmaInstEncargado.limpiar();
    firmaTrabajador=null;firmaEncargado=null;
  }
  function mensaje(txt,error) {
    $('ccMensaje').innerHTML='<div class="'+(error?'cc-error':'cc-exito')+'">'+esc(txt)+'</div>';
  }
  function renderResumen(ctx) {
    $('ccAnio').textContent=ctx.fecha.anio;
    $('ccResumen').innerHTML=[1,2,3].map(function(p) {
      var e=estado(ctx.codigo,ctx.fecha.anio,p,ctx.fecha);
      return '<article class="cc-periodo'+(p===ctx.fecha.periodo?' actual':'')+'">' +
        '<div class="cc-periodo-topo">Periodo '+p+'</div>'+
        '<div class="cc-periodo-fechas">'+MESES[p]+' · Entrega: '+MES_ENTREGA[p]+'</div>'+
        '<span class="cc-estado '+e.clase+'">'+esc(e.texto)+'</span>'+
        '<p class="cc-info-p">'+esc(e.descripcion)+'</p></article>';
    }).join('');
  }
  function renderFormulario(ctx) {
    var r=registroActual(ctx), plan=planDelPeriodo(ctx.codigo,r), tot=entregadas(r);
    var motivoBloqueo=seleccionDisponible(ctx,r);
    $('ccDestino').innerHTML='<strong>'+ (ctx.tipo==='adelanto'?'Adelanto para':'Entrega normal del') +
      ' periodo '+ctx.periodo+' de '+ctx.anio+'</strong><br>'+MESES[ctx.periodo]+
      ' · Mes de entrega normal: '+MES_ENTREGA[ctx.periodo]+'.<br>'+
      'Fecha física del movimiento: '+fechaLegible(ctx.fecha.fecha)+'.';
    $('ccAviso').innerHTML=motivoBloqueo
      ? '<div class="cc-aviso bloqueado">🔒 '+esc(motivoBloqueo)+'</div>'
      : ctx.tipo==='adelanto'
        ? '<div class="cc-aviso">Puedes adelantar una o varias prendas. Se descontarán automáticamente de la entrega normal de '+MES_ENTREGA[ctx.periodo]+'.</div>'
        : '<div class="cc-aviso">Las prendas ya adelantadas aparecen bloqueadas. Aquí completas únicamente las cantidades pendientes del mismo registro principal.</div>';
    var HTML=Object.keys(plan).map(function (art) {
      if(!CATALOGO[art])return '';
      var planificado=Number(plan[art].cantidad)||0;
      var dado=Math.min(planificado, tot[art]||0), pendiente=planificado-dado;
      var talla=plan[art].talla||'';
      if (pendiente===0) {
        return '<div class="cc-prenda entregada"><div class="cc-check"><span>✅ '+esc(CATALOGO[art])+
          '<small>Ya entregado: '+dado+' de '+planificado+'</small></span></div><div>—</div><small>Bloqueado</small></div>';
      }
      return '<div class="cc-prenda" data-articulo="'+art+'">'+
        '<label class="cc-check"><input type="checkbox" class="cc-item-check" value="'+art+'"'+
        (ctx.tipo==='normal'&&!motivoBloqueo?' checked':'')+(motivoBloqueo?' disabled':'')+'>'+
        '<span>'+esc(CATALOGO[art])+'<small>Pendientes: '+pendiente+' de '+planificado+'</small></span></label>'+
        '<label><span class="cc-mini-label">Talla</span><input class="cc-talla" type="text" maxlength="12" value="'+esc(talla)+'" '+(motivoBloqueo?'disabled':'')+'></label>'+
        '<label><span class="cc-mini-label">Cantidad</span><input class="cc-cantidad" type="number" min="1" max="'+pendiente+'" value="'+pendiente+'" '+(motivoBloqueo?'disabled':'')+'></label></div>';
    }).join('');
    $('ccPrendas').innerHTML=HTML || '<div class="cc-sin-pendientes">Sin artículos programados para este periodo.</div>';
    $('ccBloqueEncargado').style.display=ctx.tipo==='adelanto'?'none':'';
    $('ccGuardar').disabled=!!motivoBloqueo;
    $('ccGuardar').textContent=ctx.tipo==='adelanto'?'Registrar adelanto DEMO':'Registrar entrega DEMO';
  }
  function renderHistorial(ctx) {
    var anio=ctx.fecha.anio;
    var vistos=[{anio:anio,periodo:1},{anio:anio,periodo:2},{anio:anio,periodo:3}];
    var futuro=siguiente(ctx.fecha);
    if(futuro.anio!==anio) vistos.push(futuro);
    var html=vistos.map(function (p) {
      var r=registros[clave(ctx.codigo,p.anio,p.periodo)];
      if(!r || !movimientos(r).length)return '';
      var e=estado(ctx.codigo,p.anio,p.periodo,ctx.fecha);
      var movs=movimientos(r).map(function (m,n) {
        var articulos=(m.articulos||[]).map(function(a){
          return '<li>'+esc(CATALOGO[a.id]||a.id)+' · Cantidad: '+esc(a.cantidad)+' · Talla: '+esc(a.talla||'—')+'</li>';
        }).join('');
        var firma=m.firmaTrabajador && /^data:image\/(png|jpeg);base64,[A-Za-z0-9+/=]+$/.test(m.firmaTrabajador)
          ? '<details><summary>Ver firma de quien recibe</summary><img class="cc-firma-ver" alt="Firma de quien recibe" src="'+m.firmaTrabajador+'"></details>'
          : '<small>Movimiento precargado de ejemplo, sin firma real.</small>';
        return '<div class="cc-movimiento"><strong>'+esc(m.tipo==='adelanto'?'Adelanto':'Entrega normal')+' · '+fechaLegible(m.fecha)+'</strong>'+
          '<p>Movimiento '+(n+1)+' · '+esc(m.concepto||'Sin concepto')+'</p>'+
          '<ul>'+articulos+'</ul>'+firma+'</div>';
      }).join('');
      return '<article class="cc-registro"><h4>Registro principal · P'+p.periodo+' / '+p.anio+'</h4>'+
        '<span class="cc-estado '+e.clase+'">'+esc(e.texto)+'</span>'+
        '<p>'+esc(e.descripcion)+'. Movimientos registrados: '+movimientos(r).length+'.</p>'+movs+'</article>';
    }).filter(Boolean).join('');
    $('ccHistorial').innerHTML=html || '<div class="cc-vacio">No hay movimientos registrados para este colaborador en el año simulado. Puedes crear uno o cargar el caso de ejemplo.</div>';
  }
  function render() {
    var ctx=contexto();
    if(!ctx)return;
    renderResumen(ctx);
    renderFormulario(ctx);
    renderHistorial(ctx);
    limpiarFirmas();
    $('ccMensaje').innerHTML='';
  }

  function recopilarArticulos(ctx,r) {
    var plan=planDelPeriodo(ctx.codigo,r), tot=entregadas(r), elegidos=[];
    var nodes=document.querySelectorAll('#ccPrendas .cc-item-check:checked');
    for(var i=0;i<nodes.length;i++) {
      var id=nodes[i].value, fila=nodes[i].closest('.cc-prenda');
      var cantidad=Number(fila.querySelector('.cc-cantidad').value);
      var talla=fila.querySelector('.cc-talla').value.trim();
      var max=Number(plan[id].cantidad)-(tot[id]||0);
      if(!Number.isInteger(cantidad)||cantidad<1||cantidad>max)
        throw Error('Cantidad no permitida para '+CATALOGO[id]+'. Pendiente: '+max+'.');
      if(!talla)throw Error('Indica la talla de '+CATALOGO[id]+'.');
      elegidos.push({id:id,cantidad:cantidad,talla:talla});
    }
    if(!elegidos.length)throw Error('Selecciona al menos una prenda pendiente.');
    if(ctx.tipo==='normal') {
      var cantSeleccionada=elegidos.reduce(function(acc,a){return acc+a.cantidad;},0);
      var pendienteTotal=0;
      Object.keys(plan).forEach(function(id){pendienteTotal+=Math.max(0,plan[id].cantidad-(tot[id]||0));});
      if(cantSeleccionada!==pendienteTotal)
        throw Error('La entrega normal debe completar las prendas pendientes del periodo. Deja seleccionadas todas las prendas faltantes.');
    }
    return elegidos;
  }
  function guardar() {
    try {
      var ctx=contexto();
      if(!ctx)throw Error('Selecciona un colaborador y una fecha válida.');
      var r=registroActual(ctx);
      var bloqueo=seleccionDisponible(ctx,r);
      if(bloqueo)throw Error(bloqueo);
      var concepto=$('ccConcepto').value.trim();
      if(!concepto)throw Error('Escribe el concepto del movimiento.');
      if(!firmaTrabajador)throw Error('Se requiere la firma de quien recibe.');
      if(ctx.tipo==='normal'&&!firmaEncargado)throw Error('La entrega normal requiere también la firma del encargado.');
      var articulos=recopilarArticulos(ctx,r);
      var mov={tipo:ctx.tipo,fecha:ctx.fecha.fecha,concepto:concepto,articulos:articulos,
        firmaTrabajador:firmaTrabajador,firmaEncargado:ctx.tipo==='normal'?firmaEncargado:null};
      var nuevo=r ? JSON.parse(JSON.stringify(r))
        : {codigo:ctx.codigo,anio:ctx.anio,periodo:ctx.periodo,plan:planBase(ctx.codigo),movimientos:[]};
      nuevo.movimientos.push(mov);
      var original=registros[ctx.id];
      registros[ctx.id]=nuevo;
      if(!persistir()){
        if(original)registros[ctx.id]=original;else delete registros[ctx.id];
        throw Error('El navegador no pudo guardar el movimiento. Comprueba el almacenamiento disponible.');
      }
      $('ccConcepto').value='';
      render();
      var estadoActual=estado(ctx.codigo,ctx.anio,ctx.periodo,ctx.fecha);
      mensaje('Movimiento DEMO guardado. Registro único P'+ctx.periodo+' / '+ctx.anio+': '+estadoActual.texto+'.',false);
    } catch(err) { mensaje(err.message||'Error al registrar el movimiento.',true); }
  }

  function cargarEjemplo() {
    var codigo='DEMO001', id1=clave(codigo,2027,1), id2=clave(codigo,2027,2);
    if((registros[id1]||registros[id2]) &&
      !window.confirm('Esto reemplazará los registros de ejemplo de DEMO001 para P1 y P2 de 2027. ¿Continuar?'))return;
    var plan=planBase(codigo);
    registros[id1]={codigo:codigo,anio:2027,periodo:1,plan:planBase(codigo),movimientos:[{
      tipo:'normal',fecha:'2027-04-10',concepto:'Entrega programada de abril (caso ficticio)',
      articulos:Object.keys(plan).map(function(id){return {id:id,cantidad:plan[id].cantidad,talla:plan[id].talla};}),
      firmaTrabajador:null,firmaEncargado:null
    }]};
    registros[id2]={codigo:codigo,anio:2027,periodo:2,plan:planBase(codigo),movimientos:[{
      tipo:'adelanto',fecha:'2027-04-20',concepto:'Cambio de labor: adelanto parcial para el periodo 2 (caso ficticio)',
      articulos:[{id:'botas',cantidad:1,talla:'39'}],firmaTrabajador:null,firmaEncargado:null
    }]};
    if(!persistir()){mensaje('No se pudo guardar el ejemplo en este navegador.',true);return;}
    $('ccPersona').value=codigo;
    $('ccFecha').value='2027-08-15';
    document.querySelector('input[name="ccTipo"][value="normal"]').checked=true;
    $('ccConcepto').value='Completar entrega normal de agosto después del adelanto de abril';
    render();
    $('ccConcepto').value='Completar entrega normal de agosto después del adelanto de abril';
    mensaje('Ejemplo cargado: abril adelantó las botas de P2; en agosto quedan camibuso y pantalón. Firma y registra para comprobar el bloqueo de duplicados.',false);
  }
  $('ccPersona').addEventListener('change',render);
  $('ccFecha').addEventListener('change',render);
  Array.prototype.forEach.call(document.querySelectorAll('input[name="ccTipo"]'),function(input){
    input.addEventListener('change',render);
  });
  $('ccGuardar').addEventListener('click',guardar);
  $('ccCasoEjemplo').addEventListener('click',cargarEjemplo);
  $('ccRestablecer').addEventListener('click',function(){
    if(!window.confirm('¿Borrar todos los movimientos de prueba de este navegador?'))return;
    registros={};
    try{localStorage.removeItem(STORAGE_KEY);}catch(_){}
    $('ccConcepto').value='';
    render();
    mensaje('Se reiniciaron los movimientos ficticios del simulador.',false);
  });
  render();
})();
