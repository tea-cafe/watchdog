webpackJsonp([33],{804:function(e,a,n){"use strict";function t(e){return e&&e.__esModule?e:{default:e}}Object.defineProperty(a,"__esModule",{value:!0});var r=n(7),c=t(r),d=n(130),o=t(d),u=n(314);a.default={namespace:"profile",state:{basicGoods:[],basicLoading:!0,advancedOperation1:[],advancedOperation2:[],advancedOperation3:[],advancedLoading:!0},effects:{fetchBasic:o.default.mark(function e(a,n){var t,r=n.call,c=n.put;return o.default.wrap(function(e){for(;;)switch(e.prev=e.next){case 0:return e.next=2,c({type:"changeLoading",payload:{basicLoading:!0}});case 2:return e.next=4,r(u.queryBasicProfile);case 4:return t=e.sent,e.next=7,c({type:"show",payload:t});case 7:return e.next=9,c({type:"changeLoading",payload:{basicLoading:!1}});case 9:case"end":return e.stop()}},e,this)}),fetchAdvanced:o.default.mark(function e(a,n){var t,r=n.call,c=n.put;return o.default.wrap(function(e){for(;;)switch(e.prev=e.next){case 0:return e.next=2,c({type:"changeLoading",payload:{advancedLoading:!0}});case 2:return e.next=4,r(u.queryAdvancedProfile);case 4:return t=e.sent,e.next=7,c({type:"show",payload:t});case 7:return e.next=9,c({type:"changeLoading",payload:{advancedLoading:!1}});case 9:case"end":return e.stop()}},e,this)})},reducers:{show:function(e,a){var n=a.payload;return(0,c.default)({},e,n)},changeLoading:function(e,a){var n=a.payload;return(0,c.default)({},e,n)}}},e.exports=a.default}});