(()=>{"use strict";(()=>{const e=window.wp.element,t=window.wp.i18n,n=window.wp.components,o=()=>(function(){const[t,n]=(0,e.useState)({}),o=window.EcoModeSettings||{};(0,e.useEffect)((()=>{o&&n(o)}),[o])}(),(0,e.createElement)(e.Fragment,null,(0,e.createElement)(n.PanelBody,{initialOpen:!0,title:(0,t.__)("Eco Mode Settings")},(0,e.createElement)("div",{className:"settings-panel-wrapper"},"Settings here")))),c=()=>(0,e.createElement)(o,null);document.addEventListener("DOMContentLoaded",(()=>{const t=document.getElementById("eco-mode-settings");t&&(0,e.render)((0,e.createElement)(c,null),t)}))})()})();