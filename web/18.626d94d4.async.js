webpackJsonp([18],{1076:function(e,t,n){"use strict";Object.defineProperty(t,"__esModule",{value:!0});var o=n(192);n.n(o)},1498:function(e,t,n){"use strict";function o(e){var t=e[e.length-1];if(t)return t.title}function r(e){var t=e||"";t!==document.title&&(document.title=t)}function a(){}var i=n(5),u=n(8),l=n(1499);a.prototype=Object.create(i.Component.prototype),a.displayName="DocumentTitle",a.propTypes={title:u.string.isRequired},a.prototype.render=function(){return this.props.children?i.Children.only(this.props.children):null},e.exports=l(o,r)(a)},1499:function(e,t,n){"use strict";function o(e){return e&&e.__esModule?e:{default:e}}function r(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}function a(e,t){if(!e)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return!t||"object"!=typeof t&&"function"!=typeof t?e:t}function i(e,t){if("function"!=typeof t&&null!==t)throw new TypeError("Super expression must either be null or a function, not "+typeof t);e.prototype=Object.create(t&&t.prototype,{constructor:{value:e,enumerable:!1,writable:!0,configurable:!0}}),t&&(Object.setPrototypeOf?Object.setPrototypeOf(e,t):e.__proto__=t)}var u=n(5),l=o(u),c=n(1500),f=o(c),p=n(838),s=o(p);e.exports=function(e,t,n){function o(e){return e.displayName||e.name||"Component"}if("function"!=typeof e)throw new Error("Expected reducePropsToState to be a function.");if("function"!=typeof t)throw new Error("Expected handleStateChangeOnClient to be a function.");if(void 0!==n&&"function"!=typeof n)throw new Error("Expected mapStateOnServer to either be undefined or a function.");return function(c){function p(){h=e(d.map(function(e){return e.props})),m.canUseDOM?t(h):n&&(h=n(h))}if("function"!=typeof c)throw new Error("Expected WrappedComponent to be a React component.");var d=[],h=void 0,m=function(e){function t(){return r(this,t),a(this,e.apply(this,arguments))}return i(t,e),t.peek=function(){return h},t.rewind=function(){if(t.canUseDOM)throw new Error("You may only call rewind() on the server. Call peek() to read the current state.");var e=h;return h=void 0,d=[],e},t.prototype.shouldComponentUpdate=function(e){return!(0,s.default)(e,this.props)},t.prototype.componentWillMount=function(){d.push(this),p()},t.prototype.componentDidUpdate=function(){p()},t.prototype.componentWillUnmount=function(){var e=d.indexOf(this);d.splice(e,1),p()},t.prototype.render=function(){return l.default.createElement(c,this.props)},t}(u.Component);return m.displayName="SideEffect("+o(c)+")",m.canUseDOM=f.default.canUseDOM,m}}},1500:function(e,t,n){var o;!function(){"use strict";var r=!("undefined"==typeof window||!window.document||!window.document.createElement),a={canUseDOM:r,canUseWorkers:"undefined"!=typeof Worker,canUseEventListeners:r&&!(!window.addEventListener&&!window.attachEvent),canUseViewport:r&&!!window.screen};void 0!==(o=function(){return a}.call(t,n,t,e))&&(e.exports=o)}()},1504:function(e,t,n){"use strict";function o(e){return e&&e.__esModule?e:{default:e}}Object.defineProperty(t,"__esModule",{value:!0});var r=n(5),a=o(r),i=n(94),u=o(i),l=n(1505),c=o(l);t.default=function(e){var t=e.className,n=e.links,o=e.copyright,r=(0,u.default)(c.default.globalFooter,t);return a.default.createElement("div",{className:r},n&&a.default.createElement("div",{className:c.default.links},n.map(function(e){return a.default.createElement("a",{key:e.title,target:e.blankTarget?"_blank":"_self",href:e.href},e.title)})),o&&a.default.createElement("div",{className:c.default.copyright},o))},e.exports=t.default},1505:function(e,t){e.exports={globalFooter:"globalFooter___3DBsQ",links:"links___6ev0g",copyright:"copyright___2RCkh"}},1653:function(e,t){e.exports={container:"container___13qaB",top:"top___15P5h",header:"header___wZzTk",logo:"logo___3ETkL",title:"title___1S-Sy",desc:"desc___2SfO0",footer:"footer___1_Jtj"}},831:function(e,t,n){"use strict";function o(e){return e&&e.__esModule?e:{default:e}}Object.defineProperty(t,"__esModule",{value:!0});var r,a,i=n(312),u=o(i),l=n(42),c=o(l),f=n(43),p=o(f),s=n(47),d=o(s),h=n(48),m=o(h),y=n(311),_=o(y);n(1076);var v=n(5),w=o(v),E=n(8),b=o(E),g=n(314),k=n(1498),O=o(k),x=n(1504),C=o(x),j=n(1653),N=o(j),U=w.default.createElement("div",null,"Copyright ",w.default.createElement(_.default,{type:"copyright"})," 2017 \u591a\u5f97\u7ba1\u7406\u540e\u53f0"),M=(a=r=function(e){function t(){return(0,c.default)(this,t),(0,d.default)(this,(t.__proto__||(0,u.default)(t)).apply(this,arguments))}return(0,m.default)(t,e),(0,p.default)(t,[{key:"getChildContext",value:function(){return{location:this.props.location}}},{key:"getPageTitle",value:function(){var e=this.props,t=e.getRouteData,n=e.location,o=n.pathname,r="\u7ba1\u7406\u540e\u53f0";return t("UserLayout").forEach(function(e){e.path===o&&(r=e.name+" - \u7ba1\u7406\u540e\u53f0")}),r}},{key:"render",value:function(){var e=this.props.getRouteData;return w.default.createElement(O.default,{title:this.getPageTitle()},w.default.createElement("div",{className:N.default.container},w.default.createElement("div",{className:N.default.top},w.default.createElement("div",{className:N.default.header},w.default.createElement(g.Link,{to:"/"},w.default.createElement("img",{alt:"",className:N.default.logo,src:"http://admin.zhiweihl.com/static/dollar_symbol_96px.png"}),w.default.createElement("span",{className:N.default.title},"\u591a\u5f97\u7ba1\u7406\u540e\u53f0"))),w.default.createElement("div",{className:N.default.desc})),e("UserLayout").map(function(e){return w.default.createElement(g.Route,{exact:e.exact,key:e.path,path:e.path,component:e.component})}),w.default.createElement(C.default,{className:N.default.footer,copyright:U})))}}]),t}(w.default.PureComponent),r.childContextTypes={location:b.default.object},a);t.default=M,e.exports=t.default},838:function(e,t){e.exports=function(e,t,n,o){var r=n?n.call(o,e,t):void 0;if(void 0!==r)return!!r;if(e===t)return!0;if("object"!=typeof e||!e||"object"!=typeof t||!t)return!1;var a=Object.keys(e),i=Object.keys(t);if(a.length!==i.length)return!1;for(var u=Object.prototype.hasOwnProperty.bind(t),l=0;l<a.length;l++){var c=a[l];if(!u(c))return!1;var f=e[c],p=t[c];if(!1===(r=n?n.call(o,f,p,c):void 0)||void 0===r&&f!==p)return!1}return!0}}});