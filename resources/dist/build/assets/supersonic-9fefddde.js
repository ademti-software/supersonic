var C=(t,e,i)=>{if(e.has(t))throw TypeError("Cannot add the same private member more than once");e instanceof WeakSet?e.add(t):e.set(t,i)};var _=typeof globalThis<"u"?globalThis:typeof window<"u"?window:typeof global<"u"?global:typeof self<"u"?self:{};function D(t){return t&&t.__esModule&&Object.prototype.hasOwnProperty.call(t,"default")?t.default:t}var K="Expected a function",E=0/0,M="[object Symbol]",H=/^\s+|\s+$/g,U=/^[-+]0x[0-9a-f]+$/i,W=/^0b[01]+$/i,q=/^0o[0-7]+$/i,X=parseInt,G=typeof _=="object"&&_&&_.Object===Object&&_,z=typeof self=="object"&&self&&self.Object===Object&&self,J=G||z||Function("return this")(),Q=Object.prototype,Y=Q.toString,Z=Math.max,ee=Math.min,k=function(){return J.Date.now()};function te(t,e,i){var n,c,h,d,o,s,a=0,y=!1,f=!1,l=!0;if(typeof t!="function")throw new TypeError(K);e=L(e)||0,x(i)&&(y=!!i.leading,f="maxWait"in i,h=f?Z(L(i.maxWait)||0,e):h,l="trailing"in i?!!i.trailing:l);function p(r){var u=n,m=c;return n=c=void 0,a=r,d=t.apply(m,u),d}function b(r){return a=r,o=setTimeout(v,e),y?p(r):d}function A(r){var u=r-s,m=r-a,I=e-u;return f?ee(I,h-m):I}function S(r){var u=r-s,m=r-a;return s===void 0||u>=e||u<0||f&&m>=h}function v(){var r=k();if(S(r))return $(r);o=setTimeout(v,A(r))}function $(r){return o=void 0,l&&n?p(r):(n=c=void 0,d)}function V(){o!==void 0&&clearTimeout(o),a=0,n=s=c=o=void 0}function T(){return o===void 0?d:$(k())}function g(){var r=k(),u=S(r);if(n=arguments,c=this,s=r,u){if(o===void 0)return b(s);if(f)return o=setTimeout(v,e),p(s)}return o===void 0&&(o=setTimeout(v,e)),d}return g.cancel=V,g.flush=T,g}function ie(t,e,i){var n=!0,c=!0;if(typeof t!="function")throw new TypeError(K);return x(i)&&(n="leading"in i?!!i.leading:n,c="trailing"in i?!!i.trailing:c),te(t,e,{leading:n,maxWait:e,trailing:c})}function x(t){var e=typeof t;return!!t&&(e=="object"||e=="function")}function ne(t){return!!t&&typeof t=="object"}function re(t){return typeof t=="symbol"||ne(t)&&Y.call(t)==M}function L(t){if(typeof t=="number")return t;if(re(t))return E;if(x(t)){var e=typeof t.valueOf=="function"?t.valueOf():t;t=x(e)?e+"":e}if(typeof t!="string")return t===0?t:+t;t=t.replace(H,"");var i=W.test(t);return i||q.test(t)?X(t.slice(2),i?2:8):U.test(t)?E:+t}var se=ie;const ce=D(se);var oe="Expected a function",N=0/0,ae="[object Symbol]",le=/^\s+|\s+$/g,ue=/^[-+]0x[0-9a-f]+$/i,de=/^0b[01]+$/i,fe=/^0o[0-7]+$/i,he=parseInt,me=typeof _=="object"&&_&&_.Object===Object&&_,pe=typeof self=="object"&&self&&self.Object===Object&&self,ve=me||pe||Function("return this")(),_e=Object.prototype,ye=_e.toString,ge=Math.max,be=Math.min,j=function(){return ve.Date.now()};function Se(t,e,i){var n,c,h,d,o,s,a=0,y=!1,f=!1,l=!0;if(typeof t!="function")throw new TypeError(oe);e=P(e)||0,O(i)&&(y=!!i.leading,f="maxWait"in i,h=f?ge(P(i.maxWait)||0,e):h,l="trailing"in i?!!i.trailing:l);function p(r){var u=n,m=c;return n=c=void 0,a=r,d=t.apply(m,u),d}function b(r){return a=r,o=setTimeout(v,e),y?p(r):d}function A(r){var u=r-s,m=r-a,I=e-u;return f?be(I,h-m):I}function S(r){var u=r-s,m=r-a;return s===void 0||u>=e||u<0||f&&m>=h}function v(){var r=j();if(S(r))return $(r);o=setTimeout(v,A(r))}function $(r){return o=void 0,l&&n?p(r):(n=c=void 0,d)}function V(){o!==void 0&&clearTimeout(o),a=0,n=s=c=o=void 0}function T(){return o===void 0?d:$(j())}function g(){var r=j(),u=S(r);if(n=arguments,c=this,s=r,u){if(o===void 0)return b(s);if(f)return o=setTimeout(v,e),p(s)}return o===void 0&&(o=setTimeout(v,e)),d}return g.cancel=V,g.flush=T,g}function O(t){var e=typeof t;return!!t&&(e=="object"||e=="function")}function $e(t){return!!t&&typeof t=="object"}function Ie(t){return typeof t=="symbol"||$e(t)&&ye.call(t)==ae}function P(t){if(typeof t=="number")return t;if(Ie(t))return N;if(O(t)){var e=typeof t.valueOf=="function"?t.valueOf():t;t=O(e)?e+"":e}if(typeof t!="string")return t===0?t:+t;t=t.replace(le,"");var i=de.test(t);return i||fe.test(t)?he(t.slice(2),i?2:8):ue.test(t)?N:+t}var xe=Se;const Ae=D(xe);function w(t,e,i,n,c,h,d,o){var s=typeof t=="function"?t.options:t;e&&(s.render=e,s.staticRenderFns=i,s._compiled=!0),n&&(s.functional=!0),h&&(s._scopeId="data-v-"+h);var a;if(d?(a=function(l){l=l||this.$vnode&&this.$vnode.ssrContext||this.parent&&this.parent.$vnode&&this.parent.$vnode.ssrContext,!l&&typeof __VUE_SSR_CONTEXT__<"u"&&(l=__VUE_SSR_CONTEXT__),c&&c.call(this,l),l&&l._registeredComponents&&l._registeredComponents.add(d)},s._ssrRegister=a):c&&(a=o?function(){c.call(this,(s.functional?this.parent:this).$root.$options.shadowRoot)}:c),a)if(s.functional){s._injectStyles=a;var y=s.render;s.render=function(p,b){return a.call(b),y(p,b)}}else{var f=s.beforeCreate;s.beforeCreate=f?[].concat(f,a):[a]}return{exports:t,options:s}}const Ve={props:{actions:{type:Object,required:!0},isVisible:{type:Boolean,required:!0},mode:{type:String,required:!0}},data(){return{filterVal:"",filteredActions:{},selectedIdx:0,searchVal:"",errorSearching:!1,searchResults:[],selectedSearchIdx:0,isSearching:!1,searchUrl:""}},mounted(){this.debouncedFilter=ce(()=>{this.filter()},100),this.filter(),this.debouncedSearch=Ae(()=>{this.search()},300)},beforeUnmount(){this.debouncedFilter.cancel()},watch:{filterVal(){this.debouncedFilter()},searchVal(){this.debouncedSearch()}},methods:{filter(){this.filteredActions={};var t=this.filterVal.toLowerCase(),e=!1,i=0;this.selectedIdx=0;for(var n in this.actions)this.actions[n].searchName.includes(t)&&(this.filteredActions[i]=Object.assign({isSelected:!e,...this.actions[n]}),e=e||this.filteredActions[i].isSelected,i++)},async search(){this.isSearching=!0,this.errorSearching=!1,this.searchResults=[],this.searchVal.toLowerCase();const t=await fetch(this.searchUrl+"?"+new URLSearchParams({s:this.searchVal})).then(e=>(this.selectedSearchIdx=0,e.ok?e.json():(this.errorSearching=!0,[]))).catch(()=>(this.errorSearching=!0,[]));this.searchResults=await t,this.searchResults.length>0&&(this.searchResults.isSelected=!0),this.isSearching=!1},toggleVisibility(){this.isVisible||(this.filterVal="",this.mode="main"),this.isVisible=!this.isVisible,this.isVisible&&Vue.nextTick(()=>{document.getElementById("supersonic-filter").focus()})},visibilityHide(){this.isVisible=!1},mainSelectNext(){this.selectedIdx+1<Object.keys(this.filteredActions).length&&this.selectedIdx++,document.getElementById("action-"+this.selectedIdx).scrollIntoView({block:"center"})},mainSelectPrevious(){this.selectedIdx>0&&this.selectedIdx--,document.getElementById("action-"+this.selectedIdx).scrollIntoView({block:"center"})},leaveSearchAction(){this.mode="main"},selectNextSearchResult(){this.selectedSearchIdx+1<this.searchResults.length&&this.selectedSearchIdx++,document.getElementById("action-"+this.selectedSearchIdx).scrollIntoView({block:"center"})},selectPreviousSearchResult(){this.selectedSearchIdx>0&&this.selectedSearchIdx--,document.getElementById("action-"+this.selectedSearchIdx).scrollIntoView({block:"center"})},doAction(t){var n,c;var e=null;if(this.mode==="main"?e=(n=this.filteredActions[this.selectedIdx])==null?void 0:n.actions:e=(c=this.searchResults[this.selectedSearchIdx])==null?void 0:c.actions,typeof e[t]>"u")return;const i=e[t];if(i.type==="link"){if(typeof i.url>"u")return;window.location=i.url;return}if(i.type==="search"){this.searchVal="",this.searchResults=[],this.isSearching=!1,this.searchUrl=i.url,this.mode="search",Vue.nextTick(()=>{document.getElementById("supersonic-filter").focus()});return}},clickAction(t){this.mode==="main"?(this.selectedIdx=t,this.doAction("primary")):(this.selectedSearchIdx=t,this.doAction("primary"))}}};var Te=function(){var e=this,i=e._self._c;return i("div",{directives:[{name:"show",rawName:"v-show",value:e.isVisible,expression:"isVisible"}],staticClass:"modal-container"},[i("div",{staticClass:"modal"},[i("h2",[e._v(e._s(e.__("Supersonic")))]),e.mode==="main"?i("div",[i("p",[i("input",{directives:[{name:"model",rawName:"v-model",value:e.filterVal,expression:"filterVal"}],attrs:{type:"text",id:"supersonic-filter"},domProps:{value:e.filterVal},on:{input:function(n){n.target.composing||(e.filterVal=n.target.value)}}})]),i("supersonic-action-list",{attrs:{actions:e.filteredActions,selectedIdx:e.selectedIdx},on:{clickAction:e.clickAction}}),i("supersonic-key-listener",{attrs:{isVisible:e.isVisible},on:{toggleVisibility:function(n){return e.toggleVisibility()},visibilityHide:function(n){return e.visibilityHide()},selectNext:function(n){return e.mainSelectNext()},selectPrevious:function(n){return e.mainSelectPrevious()},primaryAction:function(n){return e.doAction("primary")},secondaryAction:function(n){return e.doAction("secondary")},tertiaryAction:function(n){return e.doAction("tertiary")}}})],1):e._e(),e.mode==="search"?i("div",[i("p",[i("input",{directives:[{name:"model",rawName:"v-model",value:e.searchVal,expression:"searchVal"}],attrs:{type:"text",id:"supersonic-filter"},domProps:{value:e.searchVal},on:{input:function(n){n.target.composing||(e.searchVal=n.target.value)}}})]),!e.isSearching&&this.searchResults.length>0?i("supersonic-action-list",{attrs:{actions:e.searchResults,selectedIdx:e.selectedSearchIdx},on:{clickAction:e.clickAction}}):e._e(),!e.isSearching&&this.searchResults.length<1?i("div",{staticClass:"no-results-container"},[this.errorSearching?i("p",[e._v(" "+e._s(e.__("There was an error fetching results. Sorry."))+" ")]):e._e(),!this.errorSearching&&this.searchVal.length>0?i("p",[e._v(" "+e._s(e.__("No results"))+" ")]):e._e(),!this.errorSearching&&this.searchVal.length<1?i("p",[e._v(" "+e._s(e.__("Type to search in "))+e._s(this.filteredActions[e.selectedIdx].path+" » "+this.filteredActions[e.selectedIdx].name)+" ")]):e._e()]):e._e(),e.isSearching?i("div",{staticClass:"spinner-container"},[i("div",[e._m(0),i("p",[e._v(" "+e._s(e.__("Searching..."))+" ")])])]):e._e(),i("supersonic-key-listener",{attrs:{isVisible:e.isVisible},on:{toggleVisibility:function(n){return e.toggleVisibility()},visibilityHide:function(n){return e.visibilityHide()},selectNext:function(n){return e.selectNextSearchResult()},selectPrevious:function(n){return e.selectPreviousSearchResult()},primaryAction:function(n){return e.doAction("primary")},secondaryAction:function(n){return e.doAction("secondary")},tertiaryAction:function(n){return e.doAction("tertiary")},backAction:function(n){return e.leaveSearchAction()}}})],1):e._e()])])},ke=[function(){var t=this,e=t._self._c;return e("div",{staticClass:"lds-ring"},[e("div"),e("div"),e("div"),e("div")])}],je=w(Ve,Te,ke,!1,null,"c41118e5",null,null);const Oe=je.exports;const Re={props:{actions:{type:Object,required:!0},selectedIdx:{type:Number,required:!0}},data(){return{}}};var we=function(){var e=this,i=e._self._c;return i("div",{staticClass:"list-container"},[i("ul",e._l(e.actions,function(n,c){return i("li",{class:"indent-"+n.depth+" "+(Number(c)===e.selectedIdx?"action-selected":""),attrs:{id:"action-"+c},on:{click:function(h){return e.$emit("clickAction",c)}}},[i("span",{staticClass:"action-icon",domProps:{innerHTML:e._s(n.svgIcon)}}),e._v(" "+e._s(n.name)+" "),i("span",{staticClass:"action-path"},[e._v(e._s(n.path))])])}),0)])},Ce=[],Ee=w(Re,we,Ce,!1,null,"39e22802",null,null);const Le=Ee.exports,Ne={props:{isVisible:{type:Boolean,required:!0}},data(){return{}},mounted(){document.addEventListener("keydown",t=>{if(!(!t.target instanceof HTMLElement&&!t.target instanceof document)){if((t.metaKey||t.ctrlKey)&&t.code==="KeyK")return this.$emit("toggleVisibility"),t.preventDefault();if(t.code==="Escape")return this.$emit("visibilityHide"),t.preventDefault();if(this.isVisible){if(t.code==="ArrowDown")return this.$emit("selectNext"),t.preventDefault();if(t.code==="ArrowUp")return this.$emit("selectPrevious"),t.preventDefault();if(!(t.metaKey||t.ctrlKey)&&t.code==="Enter")return this.$emit("primaryAction"),t.preventDefault();if((t.metaKey||t.ctrlKey)&&t.code==="Enter")return this.$emit("secondaryAction"),t.preventDefault();if((t.metaKey||t.ctrlKey)&&t.code==="ArrowRight")return this.$emit("tertiaryAction"),t.preventDefault();if((t.metaKey||t.ctrlKey)&&t.code==="ArrowLeft")return this.$emit("backAction"),t.preventDefault()}}})}};var Pe=function(){var e=this,i=e._self._c;return i("div")},Fe=[],Be=w(Ne,Pe,Fe,!1,null,null,null,null);const De=Be.exports;var R;class Ke{constructor(){C(this,R,void 0)}async init(){const e=await fetch("/cp/!/ademti-apps/supersonic/actions").then(i=>i.ok?i.json():{}).catch(()=>({}));this.data=await e}get(){return this.data}}R=new WeakMap;Statamic.booting(()=>{Statamic.$components.register("supersonic-app",Oe),Statamic.$components.register("supersonic-action-list",Le),Statamic.$components.register("supersonic-key-listener",De)});const F=new Ke;async function B(){await F.init(),Statamic.$components.append("supersonic-app",{props:{actions:F.get(),isVisible:!1,mode:"main"}})}document.readyState!=="loading"?B():document.addEventListener("DOMContentLoaded",B);
