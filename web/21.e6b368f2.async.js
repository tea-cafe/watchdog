webpackJsonp([21],{809:function(e,a,t){"use strict";function n(e){return e&&e.__esModule?e:{default:e}}Object.defineProperty(a,"__esModule",{value:!0});var r=t(7),u=n(r),d=t(130),o=n(d),c=t(313);a.default={namespace:"withdraw",state:{data:{list:[],pagination:{}},loading:!0},effects:{fetch:o.default.mark(function e(a,t){var n,r=a.payload,u=t.call,d=t.put;return o.default.wrap(function(e){for(;;)switch(e.prev=e.next){case 0:return e.next=2,d({type:"changeLoading",payload:!0});case 2:return e.next=4,u(c.queryWithdraw,r);case 4:return n=e.sent,e.next=7,d({type:"save",payload:n.data});case 7:return e.next=9,d({type:"changeLoading",payload:!1});case 9:case"end":return e.stop()}},e,this)})},reducers:{save:function(e,a){return(0,u.default)({},e,{data:a.payload})},changeLoading:function(e,a){return(0,u.default)({},e,{loading:a.payload})}}},e.exports=a.default}});