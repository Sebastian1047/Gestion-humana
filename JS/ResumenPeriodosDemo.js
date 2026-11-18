/* Resumen exclusivo de dotaciones cuatrimestrales ficticias (localStorage). */
document.addEventListener('DOMContentLoaded', function () {
  'use strict';
  var panel=document.getElementById('ndResumenInicio');
  if(!panel)return;
  var registros={};
  try {registros=JSON.parse(localStorage.getItem('gestion_humana_demo_cuatrimestres_v1')||'{}')||{};}catch(_){registros={};}
  var filas=Object.keys(registros).map(function(id){
    var r=registros[id];
    if(!r||!Array.isArray(r.movimientos)||!r.movimientos.length||!r.codigo||!r.anio||!r.periodo)return null;
    var plan=r.plan||{},previsto=0,dado={};
    Object.keys(plan).forEach(function(k){previsto+=Number(plan[k].cantidad)||0;});
    r.movimientos.forEach(function(m){(m.articulos||[]).forEach(function(a){dado[a.id]=(dado[a.id]||0)+Number(a.cantidad||0);});});
    var completado=Object.keys(plan).reduce(function(n,k){return n+Math.min(Number(plan[k].cantidad)||0,dado[k]||0);},0);
    var cerrado=r.movimientos.some(function(m){return m.tipo==='normal'||(m.tipo==='adelanto'&&m.modalidad==='completo');})||
      (r.movimientos.every(function(m){return m.tipo==='adelanto'&&!m.modalidad;})&&previsto>0&&completado===previsto);
    var estado=cerrado?'Completado':r.movimientos.every(function(m){return m.tipo==='adelanto';})?'Adelantado parcialmente':'Pendiente de completar';
    return {codigo:String(r.codigo),anio:Number(r.anio),periodo:Number(r.periodo),estado:estado,detalle:completado+' de '+previsto+' artículos',movimientos:r.movimientos.length};
  }).filter(Boolean);
  if(!filas.length)return;
  var escapeHtml=function(v){return String(v).replace(/[&<>"']/g,function(ch){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[ch];});};
  var nombres={DEMO001:'Persona Demo Uno',DEMO002:'Persona Demo Dos',DEMO003:'Persona Demo Tres'};
  filas.sort(function(a,b){return b.anio-a.anio||b.periodo-a.periodo||a.codigo.localeCompare(b.codigo);});
  document.getElementById('ndCantidadDemo').textContent='('+filas.length+' registros)';
  document.getElementById('ndResumenTabla').innerHTML=filas.map(function(r){
    return '<tr><td>'+escapeHtml(nombres[r.codigo]||r.codigo)+'<small>'+escapeHtml(r.codigo)+'</small></td>'+
      '<td>P'+r.periodo+' · '+r.anio+'</td><td><strong>'+escapeHtml(r.estado)+'</strong><small>'+escapeHtml(r.detalle)+'</small></td>'+
      '<td>'+r.movimientos+'</td><td><a href="NuevaDotacion.php">Revisar</a></td></tr>';
  }).join('');
  panel.hidden=false;
});
