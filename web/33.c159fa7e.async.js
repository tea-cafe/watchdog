webpackJsonp([33],{799:function(e,t,n){"use strict";function a(e){return e&&e.__esModule?e:{default:e}}Object.defineProperty(t,"__esModule",{value:!0});var r=n(7),u=a(r),c=n(130),s=a(c),o=n(314),d=n(313);t.default={namespace:"login",state:{status:-1,list:[],loading:!1,currentUser:{code:-1}},effects:{fetchCurrent:s.default.mark(function e(t,n){var a,r=n.call,u=n.put;return s.default.wrap(function(e){for(;;)switch(e.prev=e.next){case 0:return e.next=2,r(d.queryCurrent);case 2:return a=e.sent,e.next=5,u({type:"saveCurrentUser",payload:a});case 5:case"end":return e.stop()}},e,this)}),accountSubmit:s.default.mark(function e(t,n){var a,r=t.payload,u=n.call,c=n.put;return s.default.wrap(function(e){for(;;)switch(e.prev=e.next){case 0:return e.next=2,c({type:"changeSubmitting",payload:!0});case 2:return e.next=4,u(d.accountLogin,r);case 4:if(a=e.sent,0!=a.code){e.next=10;break}return e.next=8,c({type:"saveCurrentUser",payload:{code:0,data:{email:"",username:"",account_id:""}}});case 8:return e.next=10,c(o.routerRedux.push("/"));case 10:return e.next=12,c({type:"changeLoginStatus",payload:a});case 12:return e.next=14,c({type:"changeSubmitting",payload:!1});case 14:case"end":return e.stop()}},e,this)}),logout:s.default.mark(function e(t,n){var a,r=n.call,u=n.put;return s.default.wrap(function(e){for(;;)switch(e.prev=e.next){case 0:return e.next=2,u({type:"changeLoginStatus",payload:{code:-1}});case 2:return e.next=4,r(d.accountLogout);case 4:return a=e.sent,e.next=7,u(o.routerRedux.push("/user/login"));case 7:case"end":return e.stop()}},e,this)})},reducers:{changeLoginStatus:function(e,t){var n=t.payload;return(0,u.default)({},e,{status:n.code,type:"account"})},changeSubmitting:function(e,t){var n=t.payload;return(0,u.default)({},e,{submitting:n})},saveCurrentUser:function(e,t){return(0,u.default)({},e,{currentUser:t.payload})}}},e.exports=t.default}});