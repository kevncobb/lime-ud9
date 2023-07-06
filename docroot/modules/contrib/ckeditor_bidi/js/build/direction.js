!function(e,t){"object"==typeof exports&&"object"==typeof module?module.exports=t():"function"==typeof define&&define.amd?define([],t):"object"==typeof exports?exports.CKEditor5=t():(e.CKEditor5=e.CKEditor5||{},e.CKEditor5.direction=t())}(self,(()=>(()=>{var e={"ckeditor5/src/core.js":(e,t,i)=>{e.exports=i("dll-reference CKEditor5.dll")("./src/core.js")},"ckeditor5/src/ui.js":(e,t,i)=>{e.exports=i("dll-reference CKEditor5.dll")("./src/ui.js")},"ckeditor5/src/utils.js":(e,t,i)=>{e.exports=i("dll-reference CKEditor5.dll")("./src/utils.js")},"dll-reference CKEditor5.dll":e=>{"use strict";e.exports=CKEditor5.dll}},t={};function i(r){var o=t[r];if(void 0!==o)return o.exports;var n=t[r]={exports:{}};return e[r](n,n.exports,i),n.exports}i.d=(e,t)=>{for(var r in t)i.o(t,r)&&!i.o(e,r)&&Object.defineProperty(e,r,{enumerable:!0,get:t[r]})},i.o=(e,t)=>Object.prototype.hasOwnProperty.call(e,t);var r={};return(()=>{"use strict";i.d(r,{default:()=>u});var e=i("ckeditor5/src/core.js"),t=i("ckeditor5/src/utils.js");const o="direction";let n=!0;class l extends e.Command{refresh(){const e=this.editor,i=e.locale,r=(0,t.first)(this.editor.model.document.selection.getSelectedBlocks()),o=!!e.config.get("direction.rtlDefault");this.isEnabled=!!r&&this._canBeAligned(r),o&&n&&(n=!1,this.execute()),this.isEnabled&&r.hasAttribute("direction")?this.value=r.getAttribute("direction"):this.value="rtl"===i.contentLanguageDirection?"rtl":"ltr"}execute(){const e=this.editor.model,t=e.document,i="rtl";e.change((e=>{const r=Array.from(t.selection.getSelectedBlocks()).filter((e=>this._canBeAligned(e)));r[0].getAttribute("direction")===i||!1?function(e,t){for(const i of e)t.removeAttribute(o,i)}(r,e):function(e,t,i){for(const r of e)t.setAttribute(o,i,r)}(r,e,i)}))}_canBeAligned(e){return this.editor.model.schema.checkAttribute(e,o)}}class s extends e.Plugin{static get pluginName(){return"DirectionEditing"}init(){const e=this.editor;e.model.schema.extend("$block",{allowAttributes:"direction"}),e.model.schema.setAttributeProperties("direction",{isFormatting:!0}),e.conversion.for("downcast").attributeToAttribute({model:{key:"direction",values:["rtl","ltr"]},view:{rtl:{key:"dir",value:"rtl"},ltr:{key:"dir",value:"ltr"}}}),e.conversion.for("upcast").attributeToAttribute({view:{key:"dir",value:"rtl"},model:{key:"direction",value:"rtl"}}),e.commands.add("changeDirection",new l(e))}}var c=i("ckeditor5/src/ui.js");class d extends e.Plugin{static get pluginName(){return"DirectionUI"}init(){const e=this.editor;e.ui.componentFactory.add("direction",(t=>{const i=new c.ButtonView(t),r=e.commands.get("changeDirection");return i.set({label:Drupal.t("Toggle direction"),icon:'<?xml version="1.0" encoding="UTF-8"?>\n<svg viewBox="0 0 1000 1000" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">\n  <g>\n    <path d="m303.85778,110.00001c-107.05696,0 -193.85777,86.8008 -193.85777,193.85777c0,107.05696 86.8008,193.85777 193.85777,193.85777l0,387.71554l96.92889,0l0,-678.5022l96.92889,0l0,678.5022l96.92889,0l0,-678.5022l96.92889,0l0,-96.92889l-387.71554,0l0.00001,-0.00001l-0.00002,0.00001zm581.57331,145.43289l-193.85777,193.85777l193.85777,193.85777l0,-387.71554l0,-0.00001z" />\n  </g>\n</svg>\n',tooltip:!0,isToggleable:!0}),i.bind("isEnabled").to(r),i.bind("isOn").to(r,"value",(e=>"rtl"===e)),this.listenTo(i,"execute",(()=>{e.execute("changeDirection"),e.editing.view.focus()})),i}))}}class a extends e.Plugin{static get requires(){return[s,d]}static get pluginName(){return"Direction"}}const u={Direction:a}})(),r=r.default})()));