!
  function(t, e) {
    "object" == typeof exports && "object" == typeof module ? module.exports = e() : "function" == typeof define && define.amd ? define("vue-cropper-h5", [], e) : "object" == typeof exports ? exports["vue-cropper-h5"] = e() : t["vue-cropper-h5"] = e()
  } (window, (function() {
    return function(t) {
      var e = {};
      function n(r) {
        if (e[r]) return e[r].exports;
        var o = e[r] = {
          i: r,
          l: !1,
          exports: {}
        };
        return t[r].call(o.exports, o, o.exports, n),
          o.l = !0,
          o.exports
      }
      return n.m = t,
        n.c = e,
        n.d = function(t, e, r) {
          n.o(t, e) || Object.defineProperty(t, e, {
            enumerable: !0,
            get: r
          })
        },
        n.r = function(t) {
          "undefined" != typeof Symbol && Symbol.toStringTag && Object.defineProperty(t, Symbol.toStringTag, {
            value: "Module"
          }),
            Object.defineProperty(t, "__esModule", {
              value: !0
            })
        },
        n.t = function(t, e) {
          if (1 & e && (t = n(t)), 8 & e) return t;
          if (4 & e && "object" == typeof t && t && t.__esModule) return t;
          var r = Object.create(null);
          if (n.r(r), Object.defineProperty(r, "default", {
            enumerable: !0,
            value: t
          }), 2 & e && "string" != typeof t) for (var o in t) n.d(r, o,
            function(e) {
              return t[e]
            }.bind(null, o));
          return r
        },
        n.n = function(t) {
          var e = t && t.__esModule ?
            function() {
              return t.
                default
            }:
            function() {
              return t
            };
          return n.d(e, "a", e),
            e
        },
        n.o = function(t, e) {
          return Object.prototype.hasOwnProperty.call(t, e)
        },
        n.p = "/dist/",
        n(n.s = 85)
    } ([function(t, e) {
      var n = t.exports = "undefined" != typeof window && window.Math == Math ? window: "undefined" != typeof self && self.Math == Math ? self: Function("return this")();
      "number" == typeof __g && (__g = n)
    },
      function(t, e, n) {
        var r = n(32)("wks"),
          o = n(33),
          i = n(0).Symbol,
          a = "function" == typeof i; (t.exports = function(t) {
          return r[t] || (r[t] = a && i[t] || (a ? i: o)("Symbol." + t))
        }).store = r
      },
      function(t, e) {
        var n = t.exports = {
          version: "2.6.11"
        };
        "number" == typeof __e && (__e = n)
      },
      function(t, e, n) {
        var r = n(7);
        t.exports = function(t) {
          if (!r(t)) throw TypeError(t + " is not an object!");
          return t
        }
      },
      function(t, e, n) {
        var r = n(11),
          o = n(28);
        t.exports = n(5) ?
          function(t, e, n) {
            return r.f(t, e, o(1, n))
          }: function(t, e, n) {
            return t[e] = n,
              t
          }
      },
      function(t, e, n) {
        t.exports = !n(18)((function() {
          return 7 != Object.defineProperty({},
            "a", {
              get: function() {
                return 7
              }
            }).a
        }))
      },
      function(t, e, n) {
        var r = n(0),
          o = n(2),
          i = n(9),
          a = n(4),
          c = n(12),
          s = function(t, e, n) {
            var u, p, f, h = t & s.F,
              l = t & s.G,
              d = t & s.S,
              g = t & s.P,
              v = t & s.B,
              m = t & s.W,
              y = l ? o: o[e] || (o[e] = {}),
              x = y.prototype,
              w = l ? r: d ? r[e] : (r[e] || {}).prototype;
            for (u in l && (n = e), n)(p = !h && w && void 0 !== w[u]) && c(y, u) || (f = p ? w[u] : n[u], y[u] = l && "function" != typeof w[u] ? n[u] : v && p ? i(f, r) : m && w[u] == f ?
              function(t) {
                var e = function(e, n, r) {
                  if (this instanceof t) {
                    switch (arguments.length) {
                      case 0:
                        return new t;
                      case 1:
                        return new t(e);
                      case 2:
                        return new t(e, n)
                    }
                    return new t(e, n, r)
                  }
                  return t.apply(this, arguments)
                };
                return e.prototype = t.prototype,
                  e
              } (f) : g && "function" == typeof f ? i(Function.call, f) : f, g && ((y.virtual || (y.virtual = {}))[u] = f, t & s.R && x && !x[u] && a(x, u, f)))
          };
        s.F = 1,
          s.G = 2,
          s.S = 4,
          s.P = 8,
          s.B = 16,
          s.W = 32,
          s.U = 64,
          s.R = 128,
          t.exports = s
      },
      function(t, e) {
        t.exports = function(t) {
          return "object" == typeof t ? null !== t: "function" == typeof t
        }
      },
      function(t, e) {
        t.exports = {}
      },
      function(t, e, n) {
        var r = n(10);
        t.exports = function(t, e, n) {
          if (r(t), void 0 === e) return t;
          switch (n) {
            case 1:
              return function(n) {
                return t.call(e, n)
              };
            case 2:
              return function(n, r) {
                return t.call(e, n, r)
              };
            case 3:
              return function(n, r, o) {
                return t.call(e, n, r, o)
              }
          }
          return function() {
            return t.apply(e, arguments)
          }
        }
      },
      function(t, e) {
        t.exports = function(t) {
          if ("function" != typeof t) throw TypeError(t + " is not a function!");
          return t
        }
      },
      function(t, e, n) {
        var r = n(3),
          o = n(48),
          i = n(49),
          a = Object.defineProperty;
        e.f = n(5) ? Object.defineProperty: function(t, e, n) {
          if (r(t), e = i(e, !0), r(n), o) try {
            return a(t, e, n)
          } catch(t) {}
          if ("get" in n || "set" in n) throw TypeError("Accessors not supported!");
          return "value" in n && (t[e] = n.value),
            t
        }
      },
      function(t, e) {
        var n = {}.hasOwnProperty;
        t.exports = function(t, e) {
          return n.call(t, e)
        }
      },
      function(t, e) {
        var n = {}.toString;
        t.exports = function(t) {
          return n.call(t).slice(8, -1)
        }
      },
      function(t, e, n) {
        var r = n(83);
        "string" == typeof r && (r = [[t.i, r, ""]]),
        r.locals && (t.exports = r.locals); (0, n(86).
          default)("132b436a", r, !0, {})
      },
      function(t, e) {
        var n = Math.ceil,
          r = Math.floor;
        t.exports = function(t) {
          return isNaN(t = +t) ? 0 : (t > 0 ? r: n)(t)
        }
      },
      function(t, e) {
        t.exports = function(t) {
          if (null == t) throw TypeError("Can't call method on  " + t);
          return t
        }
      },
      function(t, e) {
        t.exports = !0
      },
      function(t, e) {
        t.exports = function(t) {
          try {
            return !! t()
          } catch(t) {
            return ! 0
          }
        }
      },
      function(t, e, n) {
        var r = n(7),
          o = n(0).document,
          i = r(o) && r(o.createElement);
        t.exports = function(t) {
          return i ? o.createElement(t) : {}
        }
      },
      function(t, e, n) {
        var r = n(30),
          o = n(16);
        t.exports = function(t) {
          return r(o(t))
        }
      },
      function(t, e, n) {
        var r = n(32)("keys"),
          o = n(33);
        t.exports = function(t) {
          return r[t] || (r[t] = o(t))
        }
      },
      function(t, e, n) {
        var r = n(11).f,
          o = n(12),
          i = n(1)("toStringTag");
        t.exports = function(t, e, n) {
          t && !o(t = n ? t: t.prototype, i) && r(t, i, {
            configurable: !0,
            value: e
          })
        }
      },
      function(t, e, n) {
        "use strict";
        var r = n(10);
        function o(t) {
          var e, n;
          this.promise = new t((function(t, r) {
            if (void 0 !== e || void 0 !== n) throw TypeError("Bad Promise constructor");
            e = t,
              n = r
          })),
            this.resolve = r(e),
            this.reject = r(n)
        }
        t.exports.f = function(t) {
          return new o(t)
        }
      },
      function(t, e, n) {
        t.exports = n(44)
      },
      function(t, e, n) {
        t.exports = n(76)
      },
      function(t, e, n) {
        t.exports = n(77)
      },
      function(t, e, n) {
        "use strict";
        var r = n(17),
          o = n(6),
          i = n(50),
          a = n(4),
          c = n(8),
          s = n(51),
          u = n(22),
          p = n(57),
          f = n(1)("iterator"),
          h = !([].keys && "next" in [].keys()),
          l = function() {
            return this
          };
        t.exports = function(t, e, n, d, g, v, m) {
          s(n, e, d);
          var y, x, w, C = function(t) {
              if (!h && t in k) return k[t];
              switch (t) {
                case "keys":
                case "values":
                  return function() {
                    return new n(this, t)
                  }
              }
              return function() {
                return new n(this, t)
              }
            },
            b = e + " Iterator",
            S = "values" == g,
            A = !1,
            k = t.prototype,
            O = k[f] || k["@@iterator"] || g && k[g],
            I = O || C(g),
            E = g ? S ? C("entries") : I: void 0,
            T = "Array" == e && k.entries || O;
          if (T && (w = p(T.call(new t))) !== Object.prototype && w.next && (u(w, b, !0), r || "function" == typeof w[f] || a(w, f, l)), S && O && "values" !== O.name && (A = !0, I = function() {
            return O.call(this)
          }), r && !m || !h && !A && k[f] || a(k, f, I), c[e] = I, c[b] = l, g) if (y = {
            values: S ? I: C("values"),
            keys: v ? I: C("keys"),
            entries: E
          },
            m) for (x in y) x in k || i(k, x, y[x]);
          else o(o.P + o.F * (h || A), e, y);
          return y
        }
      },
      function(t, e) {
        t.exports = function(t, e) {
          return {
            enumerable: !(1 & t),
            configurable: !(2 & t),
            writable: !(4 & t),
            value: e
          }
        }
      },
      function(t, e, n) {
        var r = n(54),
          o = n(34);
        t.exports = Object.keys ||
          function(t) {
            return r(t, o)
          }
      },
      function(t, e, n) {
        var r = n(13);
        t.exports = Object("z").propertyIsEnumerable(0) ? Object: function(t) {
          return "String" == r(t) ? t.split("") : Object(t)
        }
      },
      function(t, e, n) {
        var r = n(15),
          o = Math.min;
        t.exports = function(t) {
          return t > 0 ? o(r(t), 9007199254740991) : 0
        }
      },
      function(t, e, n) {
        var r = n(2),
          o = n(0),
          i = o["__core-js_shared__"] || (o["__core-js_shared__"] = {}); (t.exports = function(t, e) {
          return i[t] || (i[t] = void 0 !== e ? e: {})
        })("versions", []).push({
          version: r.version,
          mode: n(17) ? "pure": "global",
          copyright: "?? 2019 Denis Pushkarev (zloirock.ru)"
        })
      },
      function(t, e) {
        var n = 0,
          r = Math.random();
        t.exports = function(t) {
          return "Symbol(".concat(void 0 === t ? "": t, ")_", (++n + r).toString(36))
        }
      },
      function(t, e) {
        t.exports = "constructor,hasOwnProperty,isPrototypeOf,propertyIsEnumerable,toLocaleString,toString,valueOf".split(",")
      },
      function(t, e, n) {
        var r = n(0).document;
        t.exports = r && r.documentElement
      },
      function(t, e, n) {
        var r = n(16);
        t.exports = function(t) {
          return Object(r(t))
        }
      },
      function(t, e, n) {
        var r = n(13),
          o = n(1)("toStringTag"),
          i = "Arguments" == r(function() {
            return arguments
          } ());
        t.exports = function(t) {
          var e, n, a;
          return void 0 === t ? "Undefined": null === t ? "Null": "string" == typeof(n = function(t, e) {
            try {
              return t[e]
            } catch(t) {}
          } (e = Object(t), o)) ? n: i ? r(e) : "Object" == (a = r(e)) && "function" == typeof e.callee ? "Arguments": a
        }
      },
      function(t, e, n) {
        var r = n(3),
          o = n(10),
          i = n(1)("species");
        t.exports = function(t, e) {
          var n, a = r(t).constructor;
          return void 0 === a || null == (n = r(a)[i]) ? e: o(n)
        }
      },
      function(t, e, n) {
        var r, o, i, a = n(9),
          c = n(68),
          s = n(35),
          u = n(19),
          p = n(0),
          f = p.process,
          h = p.setImmediate,
          l = p.clearImmediate,
          d = p.MessageChannel,
          g = p.Dispatch,
          v = 0,
          m = {},
          y = function() {
            var t = +this;
            if (m.hasOwnProperty(t)) {
              var e = m[t];
              delete m[t],
                e()
            }
          },
          x = function(t) {
            y.call(t.data)
          };
        h && l || (h = function(t) {
          for (var e = [], n = 1; arguments.length > n;) e.push(arguments[n++]);
          return m[++v] = function() {
            c("function" == typeof t ? t: Function(t), e)
          },
            r(v),
            v
        },
          l = function(t) {
            delete m[t]
          },
          "process" == n(13)(f) ? r = function(t) {
            f.nextTick(a(y, t, 1))
          }: g && g.now ? r = function(t) {
            g.now(a(y, t, 1))
          }: d ? (i = (o = new d).port2, o.port1.onmessage = x, r = a(i.postMessage, i, 1)) : p.addEventListener && "function" == typeof postMessage && !p.importScripts ? (r = function(t) {
            p.postMessage(t + "", "*")
          },
            p.addEventListener("message", x, !1)) : r = "onreadystatechange" in u("script") ?
            function(t) {
              s.appendChild(u("script")).onreadystatechange = function() {
                s.removeChild(this),
                  y.call(t)
              }
            }: function(t) {
              setTimeout(a(y, t, 1), 0)
            }),
          t.exports = {
            set: h,
            clear: l
          }
      },
      function(t, e) {
        t.exports = function(t) {
          try {
            return {
              e: !1,
              v: t()
            }
          } catch(t) {
            return {
              e: !0,
              v: t
            }
          }
        }
      },
      function(t, e, n) {
        var r = n(3),
          o = n(7),
          i = n(23);
        t.exports = function(t, e) {
          if (r(t), o(e) && e.constructor === t) return e;
          var n = i.f(t);
          return (0, n.resolve)(e),
            n.promise
        }
      },
      function(t, e, n) {
        var r = n(24);
        function o(t, e, n, o, i, a, c) {
          try {
            var s = t[a](c),
              u = s.value
          } catch(t) {
            return void n(t)
          }
          s.done ? e(u) : r.resolve(u).then(o, i)
        }
        t.exports = function(t) {
          return function() {
            var e = this,
              n = arguments;
            return new r((function(r, i) {
              var a = t.apply(e, n);
              function c(t) {
                o(a, r, i, c, s, "next", t)
              }
              function s(t) {
                o(a, r, i, c, s, "throw", t)
              }
              c(void 0)
            }))
          }
        }
      },
      function(t, e, n) {
        window,
          t.exports = function(t) {
            var e = {};
            function n(r) {
              if (e[r]) return e[r].exports;
              var o = e[r] = {
                i: r,
                l: !1,
                exports: {}
              };
              return t[r].call(o.exports, o, o.exports, n),
                o.l = !0,
                o.exports
            }
            return n.m = t,
              n.c = e,
              n.d = function(t, e, r) {
                n.o(t, e) || Object.defineProperty(t, e, {
                  enumerable: !0,
                  get: r
                })
              },
              n.r = function(t) {
                "undefined" != typeof Symbol && Symbol.toStringTag && Object.defineProperty(t, Symbol.toStringTag, {
                  value: "Module"
                }),
                  Object.defineProperty(t, "__esModule", {
                    value: !0
                  })
              },
              n.t = function(t, e) {
                if (1 & e && (t = n(t)), 8 & e) return t;
                if (4 & e && "object" == typeof t && t && t.__esModule) return t;
                var r = Object.create(null);
                if (n.r(r), Object.defineProperty(r, "default", {
                  enumerable: !0,
                  value: t
                }), 2 & e && "string" != typeof t) for (var o in t) n.d(r, o,
                  function(e) {
                    return t[e]
                  }.bind(null, o));
                return r
              },
              n.n = function(t) {
                var e = t && t.__esModule ?
                  function() {
                    return t.
                      default
                  }:
                  function() {
                    return t
                  };
                return n.d(e, "a", e),
                  e
              },
              n.o = function(t, e) {
                return Object.prototype.hasOwnProperty.call(t, e)
              },
              n.p = "",
              n(n.s = 6)
          } ([function(t, e, n) {
            var r = n(2);
            "string" == typeof r && (r = [[t.i, r, ""]]),
              n(4)(r, {
                hmr: !0,
                transform: void 0,
                insertInto: void 0
              }),
            r.locals && (t.exports = r.locals)
          },
            function(t, e, n) {
              "use strict";
              var r = n(0);
              n.n(r).a
            },
            function(t, e, n) { (t.exports = n(3)(!1)).push([t.i, '\n.vue-cropper[data-v-6dae58fd] {\n  position: relative;\n  width: 100%;\n  height: 100%;\n  box-sizing: border-box;\n  user-select: none;\n  -webkit-user-select: none;\n  -moz-user-select: none;\n  -ms-user-select: none;\n  direction: ltr;\n  touch-action: none;\n  text-align: left;\n  background-image: url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQAQMAAAAlPW0iAAAAA3NCSVQICAjb4U/gAAAABlBMVEXMzMz////TjRV2AAAACXBIWXMAAArrAAAK6wGCiw1aAAAAHHRFWHRTb2Z0d2FyZQBBZG9iZSBGaXJld29ya3MgQ1M26LyyjAAAABFJREFUCJlj+M/AgBVhF/0PAH6/D/HkDxOGAAAAAElFTkSuQmCC");\n}\n.cropper-box[data-v-6dae58fd],\n.cropper-box-canvas[data-v-6dae58fd],\n.cropper-drag-box[data-v-6dae58fd],\n.cropper-crop-box[data-v-6dae58fd],\n.cropper-face[data-v-6dae58fd] {\n  position: absolute;\n  top: 0;\n  right: 0;\n  bottom: 0;\n  left: 0;\n  user-select: none;\n}\n.cropper-box-canvas img[data-v-6dae58fd] {\n  position: relative;\n  text-align: left;\n  user-select: none;\n  transform: none;\n  max-width: none;\n  max-height: none;\n}\n.cropper-box[data-v-6dae58fd] {\n  overflow: hidden;\n}\n.cropper-move[data-v-6dae58fd] {\n  cursor: move;\n}\n.cropper-crop[data-v-6dae58fd] {\n  cursor: crosshair;\n}\n.cropper-modal[data-v-6dae58fd] {\n  background: rgba(0, 0, 0, 0.5);\n}\n.cropper-crop-box[data-v-6dae58fd] {\n  /*border: 2px solid #39f;*/\n}\n.cropper-view-box[data-v-6dae58fd] {\n  display: block;\n  overflow: hidden;\n  width: 100%;\n  height: 100%;\n  outline: 1px solid #39f;\n  outline-color: rgba(51, 153, 255, 0.75);\n  user-select: none;\n}\n.cropper-view-box img[data-v-6dae58fd] {\n  user-select: none;\n  text-align: left;\n  max-width: none;\n  max-height: none;\n}\n.cropper-face[data-v-6dae58fd] {\n  top: 0;\n  left: 0;\n  background-color: #fff;\n  opacity: 0.1;\n}\n.crop-info[data-v-6dae58fd] {\n  position: absolute;\n  left: 0px;\n  min-width: 65px;\n  text-align: center;\n  color: white;\n  line-height: 20px;\n  background-color: rgba(0, 0, 0, 0.8);\n  font-size: 12px;\n}\n.crop-line[data-v-6dae58fd] {\n  position: absolute;\n  display: block;\n  width: 100%;\n  height: 100%;\n  opacity: 0.1;\n}\n.line-w[data-v-6dae58fd] {\n  top: -3px;\n  left: 0;\n  height: 5px;\n  cursor: n-resize;\n}\n.line-a[data-v-6dae58fd] {\n  top: 0;\n  left: -3px;\n  width: 5px;\n  cursor: w-resize;\n}\n.line-s[data-v-6dae58fd] {\n  bottom: -3px;\n  left: 0;\n  height: 5px;\n  cursor: s-resize;\n}\n.line-d[data-v-6dae58fd] {\n  top: 0;\n  right: -3px;\n  width: 5px;\n  cursor: e-resize;\n}\n.crop-point[data-v-6dae58fd] {\n  position: absolute;\n  width: 8px;\n  height: 8px;\n  opacity: 0.75;\n  background-color: #39f;\n  border-radius: 100%;\n}\n.point1[data-v-6dae58fd] {\n  top: -4px;\n  left: -4px;\n  cursor: nw-resize;\n}\n.point2[data-v-6dae58fd] {\n  top: -5px;\n  left: 50%;\n  margin-left: -3px;\n  cursor: n-resize;\n}\n.point3[data-v-6dae58fd] {\n  top: -4px;\n  right: -4px;\n  cursor: ne-resize;\n}\n.point4[data-v-6dae58fd] {\n  top: 50%;\n  left: -4px;\n  margin-top: -3px;\n  cursor: w-resize;\n}\n.point5[data-v-6dae58fd] {\n  top: 50%;\n  right: -4px;\n  margin-top: -3px;\n  cursor: e-resize;\n}\n.point6[data-v-6dae58fd] {\n  bottom: -5px;\n  left: -4px;\n  cursor: sw-resize;\n}\n.point7[data-v-6dae58fd] {\n  bottom: -5px;\n  left: 50%;\n  margin-left: -3px;\n  cursor: s-resize;\n}\n.point8[data-v-6dae58fd] {\n  bottom: -5px;\n  right: -4px;\n  cursor: se-resize;\n}\n@media screen and (max-width: 500px) {\n.crop-point[data-v-6dae58fd] {\n    position: absolute;\n    width: 20px;\n    height: 20px;\n    opacity: 0.45;\n    background-color: #39f;\n    border-radius: 100%;\n}\n.point1[data-v-6dae58fd] {\n    top: -10px;\n    left: -10px;\n}\n.point2[data-v-6dae58fd],\n  .point4[data-v-6dae58fd],\n  .point5[data-v-6dae58fd],\n  .point7[data-v-6dae58fd] {\n    display: none;\n}\n.point3[data-v-6dae58fd] {\n    top: -10px;\n    right: -10px;\n}\n.point4[data-v-6dae58fd] {\n    top: 0;\n    left: 0;\n}\n.point6[data-v-6dae58fd] {\n    bottom: -10px;\n    left: -10px;\n}\n.point8[data-v-6dae58fd] {\n    bottom: -10px;\n    right: -10px;\n}\n}\n', ""])
            },
            function(t, e) {
              t.exports = function(t) {
                var e = [];
                return e.toString = function() {
                  return this.map((function(e) {
                    var n = function(t, e) {
                      var n, r = t[1] || "",
                        o = t[3];
                      if (!o) return r;
                      if (e && "function" == typeof btoa) {
                        var i = (n = o, "/*# sourceMappingURL=data:application/json;charset=utf-8;base64," + btoa(unescape(encodeURIComponent(JSON.stringify(n)))) + " */"),
                          a = o.sources.map((function(t) {
                            return "/*# sourceURL=" + o.sourceRoot + t + " */"
                          }));
                        return [r].concat(a).concat([i]).join("\n")
                      }
                      return [r].join("\n")
                    } (e, t);
                    return e[2] ? "@media " + e[2] + "{" + n + "}": n
                  })).join("")
                },
                  e.i = function(t, n) {
                    "string" == typeof t && (t = [[null, t, ""]]);
                    for (var r = {},
                           o = 0; o < this.length; o++) {
                      var i = this[o][0];
                      "number" == typeof i && (r[i] = !0)
                    }
                    for (o = 0; o < t.length; o++) {
                      var a = t[o];
                      "number" == typeof a[0] && r[a[0]] || (n && !a[2] ? a[2] = n: n && (a[2] = "(" + a[2] + ") and (" + n + ")"), e.push(a))
                    }
                  },
                  e
              }
            },
            function(t, e, n) {
              var r, o, i = {},
                a = (r = function() {
                  return window && document && document.all && !window.atob
                },
                  function() {
                    return void 0 === o && (o = r.apply(this, arguments)),
                      o
                  }),
                c = function(t, e) {
                  return e ? e.querySelector(t) : document.querySelector(t)
                },
                s = function(t) {
                  var e = {};
                  return function(t, n) {
                    if ("function" == typeof t) return t();
                    if (void 0 === e[t]) {
                      var r = c.call(this, t, n);
                      if (window.HTMLIFrameElement && r instanceof window.HTMLIFrameElement) try {
                        r = r.contentDocument.head
                      } catch(t) {
                        r = null
                      }
                      e[t] = r
                    }
                    return e[t]
                  }
                } (),
                u = null,
                p = 0,
                f = [],
                h = n(5);
              function l(t, e) {
                for (var n = 0; n < t.length; n++) {
                  var r = t[n],
                    o = i[r.id];
                  if (o) {
                    o.refs++;
                    for (var a = 0; a < o.parts.length; a++) o.parts[a](r.parts[a]);
                    for (; a < r.parts.length; a++) o.parts.push(x(r.parts[a], e))
                  } else {
                    var c = [];
                    for (a = 0; a < r.parts.length; a++) c.push(x(r.parts[a], e));
                    i[r.id] = {
                      id: r.id,
                      refs: 1,
                      parts: c
                    }
                  }
                }
              }
              function d(t, e) {
                for (var n = [], r = {},
                       o = 0; o < t.length; o++) {
                  var i = t[o],
                    a = e.base ? i[0] + e.base: i[0],
                    c = {
                      css: i[1],
                      media: i[2],
                      sourceMap: i[3]
                    };
                  r[a] ? r[a].parts.push(c) : n.push(r[a] = {
                    id: a,
                    parts: [c]
                  })
                }
                return n
              }
              function g(t, e) {
                var n = s(t.insertInto);
                if (!n) throw new Error("Couldn't find a style target. This probably means that the value for the 'insertInto' parameter is invalid.");
                var r = f[f.length - 1];
                if ("top" === t.insertAt) r ? r.nextSibling ? n.insertBefore(e, r.nextSibling) : n.appendChild(e) : n.insertBefore(e, n.firstChild),
                  f.push(e);
                else if ("bottom" === t.insertAt) n.appendChild(e);
                else {
                  if ("object" != typeof t.insertAt || !t.insertAt.before) throw new Error("[Style Loader]\n\n Invalid value for parameter 'insertAt' ('options.insertAt') found.\n Must be 'top', 'bottom', or Object.\n (https://github.com/webpack-contrib/style-loader#insertat)\n");
                  var o = s(t.insertAt.before, n);
                  n.insertBefore(e, o)
                }
              }
              function v(t) {
                if (null === t.parentNode) return ! 1;
                t.parentNode.removeChild(t);
                var e = f.indexOf(t);
                e >= 0 && f.splice(e, 1)
              }
              function m(t) {
                var e = document.createElement("style");
                if (void 0 === t.attrs.type && (t.attrs.type = "text/css"), void 0 === t.attrs.nonce) {
                  var r = n.nc;
                  r && (t.attrs.nonce = r)
                }
                return y(e, t.attrs),
                  g(t, e),
                  e
              }
              function y(t, e) {
                Object.keys(e).forEach((function(n) {
                  t.setAttribute(n, e[n])
                }))
              }
              function x(t, e) {
                var n, r, o, i;
                if (e.transform && t.css) {
                  if (! (i = "function" == typeof e.transform ? e.transform(t.css) : e.transform.
                  default(t.css))) return function() {};
                  t.css = i
                }
                if (e.singleton) {
                  var a = p++;
                  n = u || (u = m(e)),
                    r = b.bind(null, n, a, !1),
                    o = b.bind(null, n, a, !0)
                } else t.sourceMap && "function" == typeof URL && "function" == typeof URL.createObjectURL && "function" == typeof URL.revokeObjectURL && "function" == typeof Blob && "function" == typeof btoa ? (n = function(t) {
                  var e = document.createElement("link");
                  return void 0 === t.attrs.type && (t.attrs.type = "text/css"),
                    t.attrs.rel = "stylesheet",
                    y(e, t.attrs),
                    g(t, e),
                    e
                } (e), r = A.bind(null, n, e), o = function() {
                  v(n),
                  n.href && URL.revokeObjectURL(n.href)
                }) : (n = m(e), r = S.bind(null, n), o = function() {
                  v(n)
                });
                return r(t),
                  function(e) {
                    if (e) {
                      if (e.css === t.css && e.media === t.media && e.sourceMap === t.sourceMap) return;
                      r(t = e)
                    } else o()
                  }
              }
              t.exports = function(t, e) {
                if ("undefined" != typeof DEBUG && DEBUG && "object" != typeof document) throw new Error("The style-loader cannot be used in a non-browser environment"); (e = e || {}).attrs = "object" == typeof e.attrs ? e.attrs: {},
                e.singleton || "boolean" == typeof e.singleton || (e.singleton = a()),
                e.insertInto || (e.insertInto = "head"),
                e.insertAt || (e.insertAt = "bottom");
                var n = d(t, e);
                return l(n, e),
                  function(t) {
                    for (var r = [], o = 0; o < n.length; o++) {
                      var a = n[o]; (c = i[a.id]).refs--,
                        r.push(c)
                    }
                    for (t && l(d(t, e), e), o = 0; o < r.length; o++) {
                      var c;
                      if (0 === (c = r[o]).refs) {
                        for (var s = 0; s < c.parts.length; s++) c.parts[s]();
                        delete i[c.id]
                      }
                    }
                  }
              };
              var w, C = (w = [],
                function(t, e) {
                  return w[t] = e,
                    w.filter(Boolean).join("\n")
                });
              function b(t, e, n, r) {
                var o = n ? "": r.css;
                if (t.styleSheet) t.styleSheet.cssText = C(e, o);
                else {
                  var i = document.createTextNode(o),
                    a = t.childNodes;
                  a[e] && t.removeChild(a[e]),
                    a.length ? t.insertBefore(i, a[e]) : t.appendChild(i)
                }
              }
              function S(t, e) {
                var n = e.css,
                  r = e.media;
                if (r && t.setAttribute("media", r), t.styleSheet) t.styleSheet.cssText = n;
                else {
                  for (; t.firstChild;) t.removeChild(t.firstChild);
                  t.appendChild(document.createTextNode(n))
                }
              }
              function A(t, e, n) {
                var r = n.css,
                  o = n.sourceMap,
                  i = void 0 === e.convertToAbsoluteUrls && o; (e.convertToAbsoluteUrls || i) && (r = h(r)),
                o && (r += "\n/*# sourceMappingURL=data:application/json;base64," + btoa(unescape(encodeURIComponent(JSON.stringify(o)))) + " */");
                var a = new Blob([r], {
                    type: "text/css"
                  }),
                  c = t.href;
                t.href = URL.createObjectURL(a),
                c && URL.revokeObjectURL(c)
              }
            },
            function(t, e) {
              t.exports = function(t) {
                var e = "undefined" != typeof window && window.location;
                if (!e) throw new Error("fixUrls requires window.location");
                if (!t || "string" != typeof t) return t;
                var n = e.protocol + "//" + e.host,
                  r = n + e.pathname.replace(/\/[^\/]*$/, "/");
                return t.replace(/url\s*\(((?:[^)(]|\((?:[^)(]+|\([^)(]*\))*\))*)\)/gi, (function(t, e) {
                  var o, i = e.trim().replace(/^"(.*)"$/, (function(t, e) {
                    return e
                  })).replace(/^'(.*)'$/, (function(t, e) {
                    return e
                  }));
                  return /^(#|data:|http:\/\/|https:\/\/|file:\/\/\/|\s*$)/i.test(i) ? t: (o = 0 === i.indexOf("//") ? i: 0 === i.indexOf("/") ? n + i: r + i.replace(/^\.\//, ""), "url(" + JSON.stringify(o) + ")")
                }))
              }
            },
            function(t, e, n) {
              "use strict";
              n.r(e),
                n.d(e, "VueCropper", (function() {
                  return s
                }));
              var r = function() {
                var t = this,
                  e = t.$createElement,
                  n = t._self._c || e;
                return n("div", {
                    ref: "cropper",
                    staticClass: "vue-cropper",
                    on: {
                      mouseover: t.scaleImg,
                      mouseout: t.cancelScale
                    }
                  },
                  [t.imgs ? n("div", {
                      staticClass: "cropper-box"
                    },
                    [n("div", {
                        directives: [{
                          name: "show",
                          rawName: "v-show",
                          value: !t.loading,
                          expression: "!loading"
                        }],
                        staticClass: "cropper-box-canvas",
                        style: {
                          width: t.trueWidth + "px",
                          height: t.trueHeight + "px",
                          transform: "scale(" + t.scale + "," + t.scale + ") translate3d(" + t.x / t.scale + "px," + t.y / t.scale + "px,0)rotateZ(" + 90 * t.rotate + "deg)"
                        }
                      },
                      [n("img", {
                        ref: "cropperImg",
                        attrs: {
                          src: t.imgs,
                          alt: "cropper-img"
                        }
                      })])]) : t._e(), t._v(" "), n("div", {
                    staticClass: "cropper-drag-box",
                    class: {
                      "cropper-move": t.move && !t.crop,
                      "cropper-crop": t.crop,
                      "cropper-modal": t.cropping
                    },
                    on: {
                      mousedown: t.startMove,
                      touchstart: t.startMove
                    }
                  }), t._v(" "), n("div", {
                      directives: [{
                        name: "show",
                        rawName: "v-show",
                        value: t.cropping,
                        expression: "cropping"
                      }],
                      staticClass: "cropper-crop-box",
                      style: {
                        width: t.cropW + "px",
                        height: t.cropH + "px",
                        transform: "translate3d(" + t.cropOffsertX + "px," + t.cropOffsertY + "px,0)"
                      }
                    },
                    [n("span", {
                        staticClass: "cropper-view-box"
                      },
                      [n("img", {
                        style: {
                          width: t.trueWidth + "px",
                          height: t.trueHeight + "px",
                          transform: "scale(" + t.scale + "," + t.scale + ") translate3d(" + (t.x - t.cropOffsertX) / t.scale + "px," + (t.y - t.cropOffsertY) / t.scale + "px,0)rotateZ(" + 90 * t.rotate + "deg)"
                        },
                        attrs: {
                          src: t.imgs,
                          alt: "cropper-img"
                        }
                      })]), t._v(" "), n("span", {
                      staticClass: "cropper-face cropper-move",
                      on: {
                        mousedown: t.cropMove,
                        touchstart: t.cropMove
                      }
                    }), t._v(" "), t.info ? n("span", {
                        staticClass: "crop-info",
                        style: {
                          top: t.cropInfo.top
                        }
                      },
                      [t._v(t._s(this.cropInfo.width) + " ?? " + t._s(this.cropInfo.height))]) : t._e(), t._v(" "), t.fixedBox ? t._e() : n("span", [n("span", {
                      staticClass: "crop-line line-w",
                      on: {
                        mousedown: function(e) {
                          return t.changeCropSize(e, !1, !0, 0, 1)
                        },
                        touchstart: function(e) {
                          return t.changeCropSize(e, !1, !0, 0, 1)
                        }
                      }
                    }), t._v(" "), n("span", {
                      staticClass: "crop-line line-a",
                      on: {
                        mousedown: function(e) {
                          return t.changeCropSize(e, !0, !1, 1, 0)
                        },
                        touchstart: function(e) {
                          return t.changeCropSize(e, !0, !1, 1, 0)
                        }
                      }
                    }), t._v(" "), n("span", {
                      staticClass: "crop-line line-s",
                      on: {
                        mousedown: function(e) {
                          return t.changeCropSize(e, !1, !0, 0, 2)
                        },
                        touchstart: function(e) {
                          return t.changeCropSize(e, !1, !0, 0, 2)
                        }
                      }
                    }), t._v(" "), n("span", {
                      staticClass: "crop-line line-d",
                      on: {
                        mousedown: function(e) {
                          return t.changeCropSize(e, !0, !1, 2, 0)
                        },
                        touchstart: function(e) {
                          return t.changeCropSize(e, !0, !1, 2, 0)
                        }
                      }
                    }), t._v(" "), n("span", {
                      staticClass: "crop-point point1",
                      on: {
                        mousedown: function(e) {
                          return t.changeCropSize(e, !0, !0, 1, 1)
                        },
                        touchstart: function(e) {
                          return t.changeCropSize(e, !0, !0, 1, 1)
                        }
                      }
                    }), t._v(" "), n("span", {
                      staticClass: "crop-point point2",
                      on: {
                        mousedown: function(e) {
                          return t.changeCropSize(e, !1, !0, 0, 1)
                        },
                        touchstart: function(e) {
                          return t.changeCropSize(e, !1, !0, 0, 1)
                        }
                      }
                    }), t._v(" "), n("span", {
                      staticClass: "crop-point point3",
                      on: {
                        mousedown: function(e) {
                          return t.changeCropSize(e, !0, !0, 2, 1)
                        },
                        touchstart: function(e) {
                          return t.changeCropSize(e, !0, !0, 2, 1)
                        }
                      }
                    }), t._v(" "), n("span", {
                      staticClass: "crop-point point4",
                      on: {
                        mousedown: function(e) {
                          return t.changeCropSize(e, !0, !1, 1, 0)
                        },
                        touchstart: function(e) {
                          return t.changeCropSize(e, !0, !1, 1, 0)
                        }
                      }
                    }), t._v(" "), n("span", {
                      staticClass: "crop-point point5",
                      on: {
                        mousedown: function(e) {
                          return t.changeCropSize(e, !0, !1, 2, 0)
                        },
                        touchstart: function(e) {
                          return t.changeCropSize(e, !0, !1, 2, 0)
                        }
                      }
                    }), t._v(" "), n("span", {
                      staticClass: "crop-point point6",
                      on: {
                        mousedown: function(e) {
                          return t.changeCropSize(e, !0, !0, 1, 2)
                        },
                        touchstart: function(e) {
                          return t.changeCropSize(e, !0, !0, 1, 2)
                        }
                      }
                    }), t._v(" "), n("span", {
                      staticClass: "crop-point point7",
                      on: {
                        mousedown: function(e) {
                          return t.changeCropSize(e, !1, !0, 0, 2)
                        },
                        touchstart: function(e) {
                          return t.changeCropSize(e, !1, !0, 0, 2)
                        }
                      }
                    }), t._v(" "), n("span", {
                      staticClass: "crop-point point8",
                      on: {
                        mousedown: function(e) {
                          return t.changeCropSize(e, !0, !0, 2, 2)
                        },
                        touchstart: function(e) {
                          return t.changeCropSize(e, !0, !0, 2, 2)
                        }
                      }
                    })])])])
              };
              r._withStripped = !0;
              var o = {
                  getData: function(t) {
                    return new Promise((function(e, n) {
                      var r = {}; (function(t) {
                        var e = null;
                        return new Promise((function(n, r) {
                          if (t.src) if (/^data\:/i.test(t.src)) e = function(t) {
                            t = t.replace(/^data\:([^\;]+)\;base64,/gim, "");
                            for (var e = atob(t), n = e.length, r = new ArrayBuffer(n), o = new Uint8Array(r), i = 0; i < n; i++) o[i] = e.charCodeAt(i);

                            return r
                          } (t.src),
                            n(e);
                          else if (/^blob\:/i.test(t.src)) {
                            var o = new FileReader;
                            o.onload = function(t) {
                              e = t.target.result,
                                n(e)
                            },
                              function(t, e) {
                                var n = new XMLHttpRequest;
                                n.open("GET", t, !0),
                                  n.responseType = "blob",
                                  n.onload = function(t) {
                                    200 != this.status && 0 !== this.status ||
                                    function(t) {
                                      o.readAsArrayBuffer(t)
                                    } (this.response)
                                  },
                                  n.send()
                              } (t.src)
                            console.log(t.src);
                          } else {
                            var i = new XMLHttpRequest;
                            i.onload = function() {
                              if (200 != this.status && 0 !== this.status) throw "Could not load image";
                              e = i.response,
                                n(e),
                                i = null
                            },
                              i.open("GET", t.src, !0),
                              i.responseType = "arraybuffer",
                              i.send(null)
                          } else r("img error")
                        }))
                      })(t).then((function(t) {
                        r.arrayBuffer = t,
                          r.orientation = function(t) {
                            var e, n, r, o, i, a, c, s, u, p = new DataView(t),
                              f = p.byteLength;
                            if (255 === p.getUint8(0) && 216 === p.getUint8(1)) for (s = 2; s < f;) {
                              if (255 === p.getUint8(s) && 225 === p.getUint8(s + 1)) {
                                a = s;
                                break
                              }
                              s++
                            }
                            if (a && (n = a + 10, "Exif" ===
                            function(t, e, n) {
                              var r, o = "";
                              for (r = e, n += e; r < n; r++) o += String.fromCharCode(t.getUint8(r));
                              return o
                            } (p, a + 4, 4) && ((o = 18761 === (i = p.getUint16(n))) || 19789 === i) && 42 === p.getUint16(n + 2, o) && (r = p.getUint32(n + 4, o)) >= 8 && (c = n + r)), c) for (f = p.getUint16(c, o), u = 0; u < f; u++) if (s = c + 12 * u + 2, 274 === p.getUint16(s, o)) {
                              s += 8,
                                e = p.getUint16(s, o);
                              break
                            }
                            return e
                          } (t),
                          e(r)
                      })).
                      catch((function(t) {
                        n(t)
                      }))
                    }))
                  }
                },
                i = o,
                a = {
                  data: function() {
                    return {
                      w: 0,
                      h: 0,
                      scale: 1,
                      x: 0,
                      y: 0,
                      loading: !0,
                      trueWidth: 0,
                      trueHeight: 0,
                      move: !0,
                      moveX: 0,
                      moveY: 0,
                      crop: !1,
                      cropping: !1,
                      cropW: 0,
                      cropH: 0,
                      cropOldW: 0,
                      cropOldH: 0,
                      canChangeX: !1,
                      canChangeY: !1,
                      changeCropTypeX: 1,
                      changeCropTypeY: 1,
                      cropX: 0,
                      cropY: 0,
                      cropChangeX: 0,
                      cropChangeY: 0,
                      cropOffsertX: 0,
                      cropOffsertY: 0,
                      support: "",
                      touches: [],
                      touchNow: !1,
                      rotate: 0,
                      isIos: !1,
                      orientation: 0,
                      imgs: "",
                      coe: .2,
                      scaling: !1,
                      scalingSet: "",
                      coeStatus: "",
                      isCanShow: !0
                    }
                  },
                  props: {
                    img: {
                      type: [String, Blob, null, File],
                      default:
                        ""
                    },
                    outputSize: {
                      type: Number,
                      default:
                        1
                    },
                    outputType: {
                      type: String,
                      default:
                        "jpeg"
                    },
                    info: {
                      type: Boolean,
                      default:
                        !0
                    },
                    canScale: {
                      type: Boolean,
                      default:
                        !0
                    },
                    autoCrop: {
                      type: Boolean,
                      default:
                        !1
                    },
                    autoCropWidth: {
                      type: [Number, String],
                      default:
                        0
                    },
                    autoCropHeight: {
                      type: [Number, String],
                      default:
                        0
                    },
                    fixed: {
                      type: Boolean,
                      default:
                        !1
                    },
                    fixedNumber: {
                      type: Array,
                      default:
                        function() {
                          return [1, 1]
                        }
                    },
                    fixedBox: {
                      type: Boolean,
                      default:
                        !1
                    },
                    full: {
                      type: Boolean,
                      default:
                        !1
                    },
                    canMove: {
                      type: Boolean,
                      default:
                        !0
                    },
                    canMoveBox: {
                      type: Boolean,
                      default:
                        !0
                    },
                    original: {
                      type: Boolean,
                      default:
                        !1
                    },
                    centerBox: {
                      type: Boolean,
                      default:
                        !1
                    },
                    high: {
                      type: Boolean,
                      default:
                        !0
                    },
                    infoTrue: {
                      type: Boolean,
                      default:
                        !1
                    },
                    maxImgSize: {
                      type: Number,
                      default:
                        2e3
                    },
                    enlarge: {
                      type: [Number, String],
                      default:
                        1
                    },
                    preW: {
                      type: [Number, String],
                      default:
                        0
                    },
                    mode: {
                      type: String,
                      default:
                        "contain"
                    }
                  },
                  computed: {
                    cropInfo: function() {
                      var t = {};
                      if (t.top = this.cropOffsertY > 21 ? "-21px": "0px", t.width = this.cropW > 0 ? this.cropW: 0, t.height = this.cropH > 0 ? this.cropH: 0, this.infoTrue) {
                        var e = 1;
                        this.high && !this.full && (e = window.devicePixelRatio),
                        1 !== this.enlarge & !this.full && (e = Math.abs(Number(this.enlarge))),
                          t.width = t.width * e,
                          t.height = t.height * e,
                        this.full && (t.width = t.width / this.scale, t.height = t.height / this.scale)
                      }
                      return t.width = t.width.toFixed(0),
                        t.height = t.height.toFixed(0),
                        t
                    },
                    isIE: function() {
                      return navigator.userAgent,
                      !!window.ActiveXObject || "ActiveXObject" in window
                    },
                    passive: function() {
                      return this.isIE ? null: {
                        passive: !1
                      }
                    }
                  },
                  watch: {
                    img: function() {
                      this.checkedImg()
                    },
                    imgs: function(t) {
                      "" !== t && this.reload()
                    },
                    cropW: function() {
                      this.showPreview()
                    },
                    cropH: function() {
                      this.showPreview()
                    },
                    cropOffsertX: function() {
                      this.showPreview()
                    },
                    cropOffsertY: function() {
                      this.showPreview()
                    },
                    scale: function(t, e) {
                      this.showPreview()
                    },
                    x: function() {
                      this.showPreview()
                    },
                    y: function() {
                      this.showPreview()
                    },
                    autoCrop: function(t) {
                      t && this.goAutoCrop()
                    },
                    autoCropWidth: function() {
                      this.autoCrop && this.goAutoCrop()
                    },
                    autoCropHeight: function() {
                      this.autoCrop && this.goAutoCrop()
                    },
                    mode: function() {
                      this.checkedImg()
                    },
                    rotate: function() {
                      this.showPreview(),
                      (this.autoCrop || this.cropW > 0 || this.cropH > 0) && this.goAutoCrop(this.cropW, this.cropH)
                    }
                  },
                  methods: {
                    checkOrientationImage: function(t, e, n, r) {
                      var o = this,
                        i = document.createElement("canvas"),
                        a = i.getContext("2d");
                      switch (a.save(), e) {
                        case 2:
                          i.width = n,
                            i.height = r,
                            a.translate(n, 0),
                            a.scale( - 1, 1);
                          break;
                        case 3:
                          i.width = n,
                            i.height = r,
                            a.translate(n / 2, r / 2),
                            a.rotate(180 * Math.PI / 180),
                            a.translate( - n / 2, -r / 2);
                          break;
                        case 4:
                          i.width = n,
                            i.height = r,
                            a.translate(0, r),
                            a.scale(1, -1);
                          break;
                        case 5:
                          i.height = n,
                            i.width = r,
                            a.rotate(.5 * Math.PI),
                            a.scale(1, -1);
                          break;
                        case 6:
                          i.width = r,
                            i.height = n,
                            a.translate(r / 2, n / 2),
                            a.rotate(90 * Math.PI / 180),
                            a.translate( - n / 2, -r / 2);
                          break;
                        case 7:
                          i.height = n,
                            i.width = r,
                            a.rotate(.5 * Math.PI),
                            a.translate(n, -r),
                            a.scale( - 1, 1);
                          break;
                        case 8:
                          i.height = n,
                            i.width = r,
                            a.translate(r / 2, n / 2),
                            a.rotate( - 90 * Math.PI / 180),
                            a.translate( - n / 2, -r / 2);
                          break;
                        default:
                          i.width = n,
                            i.height = r
                      }
                      a.drawImage(t, 0, 0, n, r),
                        a.restore(),
                        i.toBlob((function(t) {
                          var e = URL.createObjectURL(t);
                          URL.revokeObjectURL(o.imgs),
                            o.imgs = e
                        }), "image/" + this.outputType, 1)
                    },
                    checkedImg: function() {
                      var t = this;
                      if (null === this.img || "" === this.img) return this.imgs = "",
                        void this.clearCrop();
                      this.loading = !0,
                        this.scale = 1,
                        this.rotate = 0,
                        this.clearCrop();
                      var e = new Image;
                      if (e.onload = function() {
                        if ("" === t.img) return t.$emit("imgLoad", "error"),
                          t.$emit("img-load", "error"),
                          !1;
                        var n = e.width,
                          r = e.height;
                        i.getData(e).then((function(o) {

                          t.orientation = o.orientation || 1;
                          var i = t.maxImgSize; ! t.orientation && n < i & r < i ? t.imgs = t.img: (n > i && (r = r / n * i, n = i), r > i && (n = n / r * i, r = i), t.checkOrientationImage(e, t.orientation, n, r))
                          ;

                        }))

                      },
                        e.onerror = function() {
                          t.$emit("imgLoad", "error"),
                            t.$emit("img-load", "error")
                        },
                      "data" !== this.img.substr(0, 4) && (e.crossOrigin = ""), this.isIE) {
                        var n = new XMLHttpRequest;
                        n.onload = function() {
                          var t = URL.createObjectURL(this.response);
                          e.src = t
                        },
                          n.open("GET", this.img, !0),
                          n.responseType = "blob",
                          n.send()
                      } else e.src = this.img
                    },
                    startMove: function(t) {
                      if (t.preventDefault(), this.move && !this.crop) {
                        if (!this.canMove) return ! 1;
                        this.moveX = (t.clientX ? t.clientX: t.touches[0].clientX) - this.x,
                          this.moveY = (t.clientY ? t.clientY: t.touches[0].clientY) - this.y,
                          t.touches ? (window.addEventListener("touchmove", this.moveImg), window.addEventListener("touchend", this.leaveImg), 2 == t.touches.length && (this.touches = t.touches, window.addEventListener("touchmove", this.touchScale), window.addEventListener("touchend", this.cancelTouchScale))) : (window.addEventListener("mousemove", this.moveImg), window.addEventListener("mouseup", this.leaveImg)),
                          this.$emit("imgMoving", {
                            moving: !0,
                            axis: this.getImgAxis()
                          }),
                          this.$emit("img-moving", {
                            moving: !0,
                            axis: this.getImgAxis()
                          })
                      } else this.cropping = !0,
                        window.addEventListener("mousemove", this.createCrop),
                        window.addEventListener("mouseup", this.endCrop),
                        window.addEventListener("touchmove", this.createCrop),
                        window.addEventListener("touchend", this.endCrop),
                        this.cropOffsertX = t.offsetX ? t.offsetX: t.touches[0].pageX - this.$refs.cropper.offsetLeft,
                        this.cropOffsertY = t.offsetY ? t.offsetY: t.touches[0].pageY - this.$refs.cropper.offsetTop,
                        this.cropX = t.clientX ? t.clientX: t.touches[0].clientX,
                        this.cropY = t.clientY ? t.clientY: t.touches[0].clientY,
                        this.cropChangeX = this.cropOffsertX,
                        this.cropChangeY = this.cropOffsertY,
                        this.cropW = 0,
                        this.cropH = 0
                    },
                    touchScale: function(t) {
                      var e = this;
                      t.preventDefault();
                      var n = this.scale,
                        r = this.touches[0].clientX,
                        o = this.touches[0].clientY,
                        i = t.touches[0].clientX,
                        a = t.touches[0].clientY,
                        c = this.touches[1].clientX,
                        s = this.touches[1].clientY,
                        u = t.touches[1].clientX,
                        p = t.touches[1].clientY,
                        f = Math.sqrt(Math.pow(r - c, 2) + Math.pow(o - s, 2)),
                        h = Math.sqrt(Math.pow(i - u, 2) + Math.pow(a - p, 2)) - f,
                        l = 1,
                        d = (l = (l = l / this.trueWidth > l / this.trueHeight ? l / this.trueHeight: l / this.trueWidth) > .1 ? .1 : l) * h;
                      if (!this.touchNow) {
                        if (this.touchNow = !0, h > 0 ? n += Math.abs(d) : h < 0 && n > Math.abs(d) && (n -= Math.abs(d)), this.touches = t.touches, setTimeout((function() {
                          e.touchNow = !1
                        }), 8), !this.checkoutImgAxis(this.x, this.y, n)) return ! 1;
                        this.scale = n
                      }
                    },
                    cancelTouchScale: function(t) {
                      window.removeEventListener("touchmove", this.touchScale)
                    },
                    moveImg: function(t) {
                      var e = this;
                      if (t.preventDefault(), t.touches && 2 === t.touches.length) return this.touches = t.touches,
                        window.addEventListener("touchmove", this.touchScale),
                        window.addEventListener("touchend", this.cancelTouchScale),
                        window.removeEventListener("touchmove", this.moveImg),
                        !1;
                      var n, r, o = t.clientX ? t.clientX: t.touches[0].clientX,
                        i = t.clientY ? t.clientY: t.touches[0].clientY;
                      n = o - this.moveX,
                        r = i - this.moveY,
                        this.$nextTick((function() {
                          if (e.centerBox) {
                            var t, o, i, a, c = e.getImgAxis(n, r, e.scale),
                              s = e.getCropAxis(),
                              u = e.trueHeight * e.scale,
                              p = e.trueWidth * e.scale;
                            switch (e.rotate) {
                              case 1:
                              case - 1 : case 3:
                              case - 3 : t = e.cropOffsertX - e.trueWidth * (1 - e.scale) / 2 + (u - p) / 2,
                                o = e.cropOffsertY - e.trueHeight * (1 - e.scale) / 2 + (p - u) / 2,
                                i = t - u + e.cropW,
                                a = o - p + e.cropH;
                                break;
                              default:
                                t = e.cropOffsertX - e.trueWidth * (1 - e.scale) / 2,
                                  o = e.cropOffsertY - e.trueHeight * (1 - e.scale) / 2,
                                  i = t - p + e.cropW,
                                  a = o - u + e.cropH
                            }
                            c.x1 >= s.x1 && (n = t),
                            c.y1 >= s.y1 && (r = o),
                            c.x2 <= s.x2 && (n = i),
                            c.y2 <= s.y2 && (r = a)
                          }
                          e.x = n,
                            e.y = r,
                            e.$emit("imgMoving", {
                              moving: !0,
                              axis: e.getImgAxis()
                            }),
                            e.$emit("img-moving", {
                              moving: !0,
                              axis: e.getImgAxis()
                            })
                        }))
                    },
                    leaveImg: function(t) {
                      window.removeEventListener("mousemove", this.moveImg),
                        window.removeEventListener("touchmove", this.moveImg),
                        window.removeEventListener("mouseup", this.leaveImg),
                        window.removeEventListener("touchend", this.leaveImg),
                        this.$emit("imgMoving", {
                          moving: !1,
                          axis: this.getImgAxis()
                        }),
                        this.$emit("img-moving", {
                          moving: !1,
                          axis: this.getImgAxis()
                        })
                    },
                    scaleImg: function() {
                      this.canScale && window.addEventListener(this.support, this.changeSize, this.passive)
                    },
                    cancelScale: function() {
                      this.canScale && window.removeEventListener(this.support, this.changeSize)
                    },
                    changeSize: function(t) {
                      var e = this;
                      t.preventDefault();
                      var n = this.scale,
                        r = t.deltaY || t.wheelDelta;
                      r = navigator.userAgent.indexOf("Firefox") > 0 ? 30 * r: r,
                      this.isIE && (r = -r);
                      var o = this.coe,
                        i = (o = o / this.trueWidth > o / this.trueHeight ? o / this.trueHeight: o / this.trueWidth) * r;
                      i < 0 ? n += Math.abs(i) : n > Math.abs(i) && (n -= Math.abs(i));
                      var a = i < 0 ? "add": "reduce";
                      if (a !== this.coeStatus && (this.coeStatus = a, this.coe = .2), this.scaling || (this.scalingSet = setTimeout((function() {
                        e.scaling = !1,
                          e.coe = e.coe += .01
                      }), 50)), this.scaling = !0, !this.checkoutImgAxis(this.x, this.y, n)) return ! 1;
                      this.scale = n
                    },
                    changeScale: function(t) {
                      var e = this.scale;
                      t = t || 1;
                      var n = 20;
                      if ((t *= n = n / this.trueWidth > n / this.trueHeight ? n / this.trueHeight: n / this.trueWidth) > 0 ? e += Math.abs(t) : e > Math.abs(t) && (e -= Math.abs(t)), !this.checkoutImgAxis(this.x, this.y, e)) return ! 1;
                      this.scale = e
                    },
                    createCrop: function(t) {
                      var e = this;
                      t.preventDefault();
                      var n = t.clientX ? t.clientX: t.touches ? t.touches[0].clientX: 0,
                        r = t.clientY ? t.clientY: t.touches ? t.touches[0].clientY: 0;
                      this.$nextTick((function() {
                        var t = n - e.cropX,
                          o = r - e.cropY;
                        if (t > 0 ? (e.cropW = t + e.cropChangeX > e.w ? e.w - e.cropChangeX: t, e.cropOffsertX = e.cropChangeX) : (e.cropW = e.w - e.cropChangeX + Math.abs(t) > e.w ? e.cropChangeX: Math.abs(t), e.cropOffsertX = e.cropChangeX + t > 0 ? e.cropChangeX + t: 0), e.fixed) {
                          var i = e.cropW / e.fixedNumber[0] * e.fixedNumber[1];
                          i + e.cropOffsertY > e.h ? (e.cropH = e.h - e.cropOffsertY, e.cropW = e.cropH / e.fixedNumber[1] * e.fixedNumber[0], e.cropOffsertX = t > 0 ? e.cropChangeX: e.cropChangeX - e.cropW) : e.cropH = i,
                            e.cropOffsertY = e.cropOffsertY
                        } else o > 0 ? (e.cropH = o + e.cropChangeY > e.h ? e.h - e.cropChangeY: o, e.cropOffsertY = e.cropChangeY) : (e.cropH = e.h - e.cropChangeY + Math.abs(o) > e.h ? e.cropChangeY: Math.abs(o), e.cropOffsertY = e.cropChangeY + o > 0 ? e.cropChangeY + o: 0)
                      }))
                    },
                    changeCropSize: function(t, e, n, r, o) {
                      t.preventDefault(),
                        window.addEventListener("mousemove", this.changeCropNow),
                        window.addEventListener("mouseup", this.changeCropEnd),
                        window.addEventListener("touchmove", this.changeCropNow),
                        window.addEventListener("touchend", this.changeCropEnd),
                        this.canChangeX = e,
                        this.canChangeY = n,
                        this.changeCropTypeX = r,
                        this.changeCropTypeY = o,
                        this.cropX = t.clientX ? t.clientX: t.touches[0].clientX,
                        this.cropY = t.clientY ? t.clientY: t.touches[0].clientY,
                        this.cropOldW = this.cropW,
                        this.cropOldH = this.cropH,
                        this.cropChangeX = this.cropOffsertX,
                        this.cropChangeY = this.cropOffsertY,
                      this.fixed && this.canChangeX && this.canChangeY && (this.canChangeY = 0),
                        this.$emit("change-crop-size", {
                          width: this.cropW,
                          height: this.cropH
                        })
                    },
                    changeCropNow: function(t) {
                      var e = this;
                      t.preventDefault();
                      var n = t.clientX ? t.clientX: t.touches ? t.touches[0].clientX: 0,
                        r = t.clientY ? t.clientY: t.touches ? t.touches[0].clientY: 0,
                        o = this.w,
                        i = this.h,
                        a = 0,
                        c = 0;
                      if (this.centerBox) {
                        var s = this.getImgAxis(),
                          u = s.x2,
                          p = s.y2;
                        a = s.x1 > 0 ? s.x1: 0,
                          c = s.y1 > 0 ? s.y1: 0,
                        o > u && (o = u),
                        i > p && (i = p)
                      }
                      this.$nextTick((function() {
                        var t = n - e.cropX,
                          s = r - e.cropY;
                        if (e.canChangeX && (1 === e.changeCropTypeX ? e.cropOldW - t > 0 ? (e.cropW = o - e.cropChangeX - t <= o - a ? e.cropOldW - t: e.cropOldW + e.cropChangeX - a, e.cropOffsertX = o - e.cropChangeX - t <= o - a ? e.cropChangeX + t: a) : (e.cropW = Math.abs(t) + e.cropChangeX <= o ? Math.abs(t) - e.cropOldW: o - e.cropOldW - e.cropChangeX, e.cropOffsertX = e.cropChangeX + e.cropOldW) : 2 === e.changeCropTypeX && (e.cropOldW + t > 0 ? (e.cropW = e.cropOldW + t + e.cropOffsertX <= o ? e.cropOldW + t: o - e.cropOffsertX, e.cropOffsertX = e.cropChangeX) : (e.cropW = o - e.cropChangeX + Math.abs(t + e.cropOldW) <= o - a ? Math.abs(t + e.cropOldW) : e.cropChangeX - a, e.cropOffsertX = o - e.cropChangeX + Math.abs(t + e.cropOldW) <= o - a ? e.cropChangeX - Math.abs(t + e.cropOldW) : a))), e.canChangeY && (1 === e.changeCropTypeY ? e.cropOldH - s > 0 ? (e.cropH = i - e.cropChangeY - s <= i - c ? e.cropOldH - s: e.cropOldH + e.cropChangeY - c, e.cropOffsertY = i - e.cropChangeY - s <= i - c ? e.cropChangeY + s: c) : (e.cropH = Math.abs(s) + e.cropChangeY <= i ? Math.abs(s) - e.cropOldH: i - e.cropOldH - e.cropChangeY, e.cropOffsertY = e.cropChangeY + e.cropOldH) : 2 === e.changeCropTypeY && (e.cropOldH + s > 0 ? (e.cropH = e.cropOldH + s + e.cropOffsertY <= i ? e.cropOldH + s: i - e.cropOffsertY, e.cropOffsertY = e.cropChangeY) : (e.cropH = i - e.cropChangeY + Math.abs(s + e.cropOldH) <= i - c ? Math.abs(s + e.cropOldH) : e.cropChangeY - c, e.cropOffsertY = i - e.cropChangeY + Math.abs(s + e.cropOldH) <= i - c ? e.cropChangeY - Math.abs(s + e.cropOldH) : c))), e.canChangeX && e.fixed) {
                          var u = e.cropW / e.fixedNumber[0] * e.fixedNumber[1];
                          u + e.cropOffsertY > i ? (e.cropH = i - e.cropOffsertY, e.cropW = e.cropH / e.fixedNumber[1] * e.fixedNumber[0]) : e.cropH = u
                        }
                        if (e.canChangeY && e.fixed) {
                          var p = e.cropH / e.fixedNumber[1] * e.fixedNumber[0];
                          p + e.cropOffsertX > o ? (e.cropW = o - e.cropOffsertX, e.cropH = e.cropW / e.fixedNumber[0] * e.fixedNumber[1]) : e.cropW = p
                        }
                        e.cropW = 0 == e.cropW ? 0 : e.cropW > 52 ? e.cropW: 52,
                          e.cropH = 0 == e.cropH ? 0 : e.cropH > 52 ? e.cropH: 52
                      }))
                    },
                    changeCropEnd: function(t) {
                      window.removeEventListener("mousemove", this.changeCropNow),
                        window.removeEventListener("mouseup", this.changeCropEnd),
                        window.removeEventListener("touchmove", this.changeCropNow),
                        window.removeEventListener("touchend", this.changeCropEnd)
                    },
                    endCrop: function() {
                      0 === this.cropW && 0 === this.cropH && (this.cropping = !1),
                        window.removeEventListener("mousemove", this.createCrop),
                        window.removeEventListener("mouseup", this.endCrop),
                        window.removeEventListener("touchmove", this.createCrop),
                        window.removeEventListener("touchend", this.endCrop)
                    },
                    startCrop: function() {
                      this.crop = !0
                    },
                    stopCrop: function() {
                      this.crop = !1
                    },
                    clearCrop: function() {
                      this.cropping = !1,
                        this.cropW = 0,
                        this.cropH = 0
                    },
                    cropMove: function(t) {
                      if (t.preventDefault(), !this.canMoveBox) return this.crop = !1,
                        this.startMove(t),
                        !1;
                      if (t.touches && 2 === t.touches.length) return this.crop = !1,
                        this.startMove(t),
                        this.leaveCrop(),
                        !1;
                      window.addEventListener("mousemove", this.moveCrop),
                        window.addEventListener("mouseup", this.leaveCrop),
                        window.addEventListener("touchmove", this.moveCrop),
                        window.addEventListener("touchend", this.leaveCrop);
                      var e, n, r = t.clientX ? t.clientX: t.touches[0].clientX,
                        o = t.clientY ? t.clientY: t.touches[0].clientY;
                      e = r - this.cropOffsertX,
                        n = o - this.cropOffsertY,
                        this.cropX = e,
                        this.cropY = n,
                        this.$emit("cropMoving", {
                          moving: !0,
                          axis: this.getCropAxis()
                        }),
                        this.$emit("crop-moving", {
                          moving: !0,
                          axis: this.getCropAxis()
                        })
                    },
                    moveCrop: function(t, e) {
                      var n = this,
                        r = 0,
                        o = 0;
                      t && (t.preventDefault(), r = t.clientX ? t.clientX: t.touches[0].clientX, o = t.clientY ? t.clientY: t.touches[0].clientY),
                        this.$nextTick((function() {
                          var t, i, a = r - n.cropX,
                            c = o - n.cropY;
                          if (e && (a = n.cropOffsertX, c = n.cropOffsertY), t = a <= 0 ? 0 : a + n.cropW > n.w ? n.w - n.cropW: a, i = c <= 0 ? 0 : c + n.cropH > n.h ? n.h - n.cropH: c, n.centerBox) {
                            var s = n.getImgAxis();
                            t <= s.x1 && (t = s.x1),
                            t + n.cropW > s.x2 && (t = s.x2 - n.cropW),
                            i <= s.y1 && (i = s.y1),
                            i + n.cropH > s.y2 && (i = s.y2 - n.cropH)
                          }
                          n.cropOffsertX = t,
                            n.cropOffsertY = i,
                            n.$emit("cropMoving", {
                              moving: !0,
                              axis: n.getCropAxis()
                            }),
                            n.$emit("crop-moving", {
                              moving: !0,
                              axis: n.getCropAxis()
                            })
                        }))
                    },
                    getImgAxis: function(t, e, n) {
                      t = t || this.x,
                        e = e || this.y,
                        n = n || this.scale;
                      var r = {
                          x1: 0,
                          x2: 0,
                          y1: 0,
                          y2: 0
                        },
                        o = this.trueWidth * n,
                        i = this.trueHeight * n;
                      switch (this.rotate) {
                        case 0:
                          r.x1 = t + this.trueWidth * (1 - n) / 2,
                            r.x2 = r.x1 + this.trueWidth * n,
                            r.y1 = e + this.trueHeight * (1 - n) / 2,
                            r.y2 = r.y1 + this.trueHeight * n;
                          break;
                        case 1:
                        case - 1 : case 3:
                        case - 3 : r.x1 = t + this.trueWidth * (1 - n) / 2 + (o - i) / 2,
                          r.x2 = r.x1 + this.trueHeight * n,
                          r.y1 = e + this.trueHeight * (1 - n) / 2 + (i - o) / 2,
                          r.y2 = r.y1 + this.trueWidth * n;
                          break;
                        default:
                          r.x1 = t + this.trueWidth * (1 - n) / 2,
                            r.x2 = r.x1 + this.trueWidth * n,
                            r.y1 = e + this.trueHeight * (1 - n) / 2,
                            r.y2 = r.y1 + this.trueHeight * n
                      }
                      return r
                    },
                    getCropAxis: function() {
                      var t = {
                        x1: 0,
                        x2: 0,
                        y1: 0,
                        y2: 0
                      };
                      return t.x1 = this.cropOffsertX,
                        t.x2 = t.x1 + this.cropW,
                        t.y1 = this.cropOffsertY,
                        t.y2 = t.y1 + this.cropH,
                        t
                    },
                    leaveCrop: function(t) {
                      window.removeEventListener("mousemove", this.moveCrop),
                        window.removeEventListener("mouseup", this.leaveCrop),
                        window.removeEventListener("touchmove", this.moveCrop),
                        window.removeEventListener("touchend", this.leaveCrop),
                        this.$emit("cropMoving", {
                          moving: !1,
                          axis: this.getCropAxis()
                        }),
                        this.$emit("crop-moving", {
                          moving: !1,
                          axis: this.getCropAxis()
                        })
                    },
                    getCropChecked: function(t) {
                      var e = this,
                        n = document.createElement("canvas"),
                        r = new Image,
                        o = this.rotate,
                        i = this.trueWidth,
                        a = this.trueHeight,
                        c = this.cropOffsertX,
                        s = this.cropOffsertY;
                      function u(t, e) {
                        n.width = Math.round(t),
                          n.height = Math.round(e)
                      }
                      r.onload = function() {
                        if (0 !== e.cropW) {
                          var p = n.getContext("2d"),
                            f = 1;
                          e.high & !e.full && (f = window.devicePixelRatio),
                          1 !== e.enlarge & !e.full && (f = Math.abs(Number(e.enlarge)));
                          var h = e.cropW * f,
                            l = e.cropH * f,
                            d = i * e.scale * f,
                            g = a * e.scale * f,
                            v = (e.x - c + e.trueWidth * (1 - e.scale) / 2) * f,
                            m = (e.y - s + e.trueHeight * (1 - e.scale) / 2) * f;
                          switch (u(h, l), p.save(), o) {
                            case 0:
                              e.full ? (u(h / e.scale, l / e.scale), p.drawImage(r, v / e.scale, m / e.scale, d / e.scale, g / e.scale)) : p.drawImage(r, v, m, d, g);
                              break;
                            case 1:
                            case - 3 : e.full ? (u(h / e.scale, l / e.scale), v = v / e.scale + (d / e.scale - g / e.scale) / 2, m = m / e.scale + (g / e.scale - d / e.scale) / 2, p.rotate(90 * o * Math.PI / 180), p.drawImage(r, m, -v - g / e.scale, d / e.scale, g / e.scale)) : (v += (d - g) / 2, m += (g - d) / 2, p.rotate(90 * o * Math.PI / 180), p.drawImage(r, m, -v - g, d, g));
                              break;
                            case 2:
                            case - 2 : e.full ? (u(h / e.scale, l / e.scale), p.rotate(90 * o * Math.PI / 180), v /= e.scale, m /= e.scale, p.drawImage(r, -v - d / e.scale, -m - g / e.scale, d / e.scale, g / e.scale)) : (p.rotate(90 * o * Math.PI / 180), p.drawImage(r, -v - d, -m - g, d, g));
                              break;
                            case 3:
                            case - 1 : e.full ? (u(h / e.scale, l / e.scale), v = v / e.scale + (d / e.scale - g / e.scale) / 2, m = m / e.scale + (g / e.scale - d / e.scale) / 2, p.rotate(90 * o * Math.PI / 180), p.drawImage(r, -m - d / e.scale, v, d / e.scale, g / e.scale)) : (v += (d - g) / 2, m += (g - d) / 2, p.rotate(90 * o * Math.PI / 180), p.drawImage(r, -m - d, v, d, g));
                              break;
                            default:
                              e.full ? (u(h / e.scale, l / e.scale), p.drawImage(r, v / e.scale, m / e.scale, d / e.scale, g / e.scale)) : p.drawImage(r, v, m, d, g)
                          }
                          p.restore()
                        } else {
                          var y = i * e.scale,
                            x = a * e.scale,
                            w = n.getContext("2d");
                          switch (w.save(), o) {
                            case 0:
                              u(y, x),
                                w.drawImage(r, 0, 0, y, x);
                              break;
                            case 1:
                            case - 3 : u(x, y),
                              w.rotate(90 * o * Math.PI / 180),
                              w.drawImage(r, 0, -x, y, x);
                              break;
                            case 2:
                            case - 2 : u(y, x),
                              w.rotate(90 * o * Math.PI / 180),
                              w.drawImage(r, -y, -x, y, x);
                              break;
                            case 3:
                            case - 1 : u(x, y),
                              w.rotate(90 * o * Math.PI / 180),
                              w.drawImage(r, -y, 0, y, x);
                              break;
                            default:
                              u(y, x),
                                w.drawImage(r, 0, 0, y, x)
                          }
                          w.restore()
                        }
                        t(n)
                      },
                      "data" !== this.img.substr(0, 4) && (r.crossOrigin = "Anonymous"),
                        r.src = this.imgs

                    },
                    getCropData: function(t) {
                      var e = this;
                      this.getCropChecked((function(n) {
                        t(n.toDataURL("image/" + e.outputType, e.outputSize))
                      }))
                    },
                    getCropBlob: function(t) {
                      var e = this;
                      this.getCropChecked((function(n) {
                        n.toBlob((function(e) {
                          return t(e)
                        }), "image/" + e.outputType, e.outputSize)
                      }))
                    },
                    showPreview: function() {
                      var t = this;
                      if (!this.isCanShow) return ! 1;
                      this.isCanShow = !1,
                        setTimeout((function() {
                          t.isCanShow = !0
                        }), 16);
                      var e = this.cropW,
                        n = this.cropH,
                        r = this.scale,
                        o = {};
                      o.div = {
                        width: "".concat(e, "px"),
                        height: "".concat(n, "px")
                      };
                      var i = (this.x - this.cropOffsertX) / r,
                        a = (this.y - this.cropOffsertY) / r;
                      o.w = e,
                        o.h = n,
                        o.url = this.imgs,
                        o.img = {
                          width: "".concat(this.trueWidth, "px"),
                          height: "".concat(this.trueHeight, "px"),
                          transform: "scale(".concat(r, ")translate3d(").concat(i, "px, ").concat(a, "px, ").concat(0, "px)rotateZ(").concat(90 * this.rotate, "deg)")
                        },
                        o.html = '\n      <div class="show-preview" style="width: '.concat(o.w, "px; height: ").concat(o.h, 'px,; overflow: hidden">\n        <div style="width: ').concat(e, "px; height: ").concat(n, 'px">\n          <img src=').concat(o.url, ' style="width: ').concat(this.trueWidth, "px; height: ").concat(this.trueHeight, "px; transform:\n          scale(").concat(r, ")translate3d(").concat(i, "px, ").concat(a, "px, ").concat(0, "px)rotateZ(").concat(90 * this.rotate, 'deg)">\n        </div>\n      </div>'),
                        this.$emit("realTime", o),
                        this.$emit("real-time", o)
                    },
                    reload: function() {
                      var t = this,
                        e = new Image;
                      e.onload = function() {
                        t.w = parseFloat(window.getComputedStyle(t.$refs.cropper).width),
                          t.h = parseFloat(window.getComputedStyle(t.$refs.cropper).height),
                          t.trueWidth = e.width,
                          t.trueHeight = e.height,
                          t.original ? t.scale = 1 : t.scale = t.checkedMode(),
                          t.$nextTick((function() {
                            t.x = -(t.trueWidth - t.trueWidth * t.scale) / 2 + (t.w - t.trueWidth * t.scale) / 2,
                              t.y = -(t.trueHeight - t.trueHeight * t.scale) / 2 + (t.h - t.trueHeight * t.scale) / 2,
                              t.loading = !1,
                            t.autoCrop && t.goAutoCrop(),
                              t.$emit("img-load", "success"),
                              t.$emit("imgLoad", "success"),
                              setTimeout((function() {
                                t.showPreview()
                              }), 20)
                          }))
                      },
                        e.onerror = function() {
                          t.$emit("imgLoad", "error"),
                            t.$emit("img-load", "error")
                        },
                        e.src = this.imgs
                    },
                    checkedMode: function() {
                      var t = 1,
                        e = (this.trueWidth, this.trueHeight),
                        n = this.mode.split(" ");
                      switch (n[0]) {
                        case "contain":
                          this.trueWidth > this.w && (t = this.w / this.trueWidth),
                          this.trueHeight * t > this.h && (t = this.h / this.trueHeight);
                          break;
                        case "cover":
                          (e *= t = this.w / this.trueWidth) < this.h && (t = (e = this.h) / this.trueHeight);
                          break;
                        default:
                          try {
                            var r = n[0];
                            if ( - 1 !== r.search("px") && (r = r.replace("px", ""), t = parseFloat(r) / this.trueWidth), -1 !== r.search("%") && (r = r.replace("%", ""), t = parseFloat(r) / 100 * this.w / this.trueWidth), 2 === n.length && "auto" === r) {
                              var o = n[1]; - 1 !== o.search("px") && (o = o.replace("px", ""), t = (e = parseFloat(o)) / this.trueHeight),
                              -1 !== o.search("%") && (o = o.replace("%", ""), t = (e = parseFloat(o) / 100 * this.h) / this.trueHeight)
                            }
                          } catch(e) {
                            t = 1
                          }
                      }
                      return t
                    },
                    goAutoCrop: function(t, e) {
                      if ("" !== this.imgs && null !== this.imgs) {
                        this.clearCrop(),
                          this.cropping = !0;
                        var n = this.w,
                          r = this.h;
                        if (this.centerBox) {
                          var o = this.trueWidth * this.scale,
                            i = this.trueHeight * this.scale;
                          n = o < n ? o: n,
                            r = i < r ? i: r
                        }
                        var a = t || parseFloat(this.autoCropWidth),
                          c = e || parseFloat(this.autoCropHeight);
                        0 !== a && 0 !== c || (a = .8 * n, c = .8 * r),
                          a = a > n ? n: a,
                          c = c > r ? r: c,
                        this.fixed && (c = a / this.fixedNumber[0] * this.fixedNumber[1]),
                        c > this.h && (a = (c = this.h) / this.fixedNumber[1] * this.fixedNumber[0]),
                          this.changeCrop(a, c)
                      }
                    },
                    changeCrop: function(t, e) {
                      var n = this;
                      if (this.centerBox) {
                        var r = this.getImgAxis();
                        t > r.x2 - r.x1 && (e = (t = r.x2 - r.x1) / this.fixedNumber[0] * this.fixedNumber[1]),
                        e > r.y2 - r.y1 && (t = (e = r.y2 - r.y1) / this.fixedNumber[1] * this.fixedNumber[0])
                      }
                      this.cropW = t,
                        this.cropH = e,
                        this.cropOffsertX = (this.w - t) / 2,
                        this.cropOffsertY = (this.h - e) / 2,
                      this.centerBox && this.$nextTick((function() {
                        n.moveCrop(null, !0)
                      }))
                    },
                    refresh: function() {
                      var t = this;
                      this.img,
                        this.imgs = "",
                        this.scale = 1,
                        this.crop = !1,
                        this.rotate = 0,
                        this.w = 0,
                        this.h = 0,
                        this.trueWidth = 0,
                        this.trueHeight = 0,
                        this.clearCrop(),
                        this.$nextTick((function() {
                          t.checkedImg()
                        }))
                    },
                    rotateLeft: function() {
                      this.rotate = this.rotate <= -3 ? 0 : this.rotate - 1
                    },
                    rotateRight: function() {
                      this.rotate = this.rotate >= 3 ? 0 : this.rotate + 1
                    },
                    rotateClear: function() {
                      this.rotate = 0
                    },
                    checkoutImgAxis: function(t, e, n) {
                      t = t || this.x,
                        e = e || this.y,
                        n = n || this.scale;
                      var r = !0;
                      if (this.centerBox) {
                        var o = this.getImgAxis(t, e, n),
                          i = this.getCropAxis();
                        o.x1 >= i.x1 && (r = !1),
                        o.x2 <= i.x2 && (r = !1),
                        o.y1 >= i.y1 && (r = !1),
                        o.y2 <= i.y2 && (r = !1)
                      }
                      return r
                    }
                  },
                  mounted: function() {
                    this.support = "onwheel" in document.createElement("div") ? "wheel": void 0 !== document.onmousewheel ? "mousewheel": "DOMMouseScroll";
                    var t = this,
                      e = navigator.userAgent;
                    this.isIOS = !!e.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/),
                    HTMLCanvasElement.prototype.toBlob || Object.defineProperty(HTMLCanvasElement.prototype, "toBlob", {
                      value: function(e, n, r) {
                        for (var o = atob(this.toDataURL(n, r).split(",")[1]), i = o.length, a = new Uint8Array(i), c = 0; c < i; c++) a[c] = o.charCodeAt(c);
                        e(new Blob([a], {
                          type: t.type || "image/png"
                        }))
                      }
                    }),
                      this.showPreview(),
                      this.checkedImg()
                  },
                  destroyed: function() {
                    window.removeEventListener("mousemove", this.moveCrop),
                      window.removeEventListener("mouseup", this.leaveCrop),
                      window.removeEventListener("touchmove", this.moveCrop),
                      window.removeEventListener("touchend", this.leaveCrop)
                  }
                };
              n(1);
              var c = function(t, e, n, r, o, i, a, c) {
                var s, u = "function" == typeof t ? t.options: t;
                if (e && (u.render = e, u.staticRenderFns = [], u._compiled = !0), u._scopeId = "data-v-" + i, s) if (u.functional) {
                  u._injectStyles = s;
                  var p = u.render;
                  u.render = function(t, e) {
                    return s.call(e),
                      p(t, e)
                  }
                } else {
                  var f = u.beforeCreate;
                  u.beforeCreate = f ? [].concat(f, s) : [s]
                }
                return {
                  exports: t,
                  options: u
                }
              } (a, r, 0, 0, 0, "6dae58fd");
              c.options.__file = "src/vue-cropper.vue";
              var s = c.exports,
                u = function(t) {
                  t.component("VueCropper", s)
                };
              "undefined" != typeof window && window.Vue && u(window.Vue),
                e.
                  default = {
                  version: "0.5.0",
                  install: u,
                  VueCropper: s,
                  vueCropper: s
                }
            }])
      },
      function(t, e, n) {
        n(45),
          n(46),
          n(58),
          n(62),
          n(74),
          n(75),
          t.exports = n(2).Promise
      },
      function(t, e) {},
      function(t, e, n) {
        "use strict";
        var r = n(47)(!0);
        n(27)(String, "String", (function(t) {
          this._t = String(t),
            this._i = 0
        }), (function() {
          var t, e = this._t,
            n = this._i;
          return n >= e.length ? {
            value: void 0,
            done: !0
          }: (t = r(e, n), this._i += t.length, {
            value: t,
            done: !1
          })
        }))
      },
      function(t, e, n) {
        var r = n(15),
          o = n(16);
        t.exports = function(t) {
          return function(e, n) {
            var i, a, c = String(o(e)),
              s = r(n),
              u = c.length;
            return s < 0 || s >= u ? t ? "": void 0 : (i = c.charCodeAt(s)) < 55296 || i > 56319 || s + 1 === u || (a = c.charCodeAt(s + 1)) < 56320 || a > 57343 ? t ? c.charAt(s) : i: t ? c.slice(s, s + 2) : a - 56320 + (i - 55296 << 10) + 65536
          }
        }
      },
      function(t, e, n) {
        t.exports = !n(5) && !n(18)((function() {
          return 7 != Object.defineProperty(n(19)("div"), "a", {
            get: function() {
              return 7
            }
          }).a
        }))
      },
      function(t, e, n) {
        var r = n(7);
        t.exports = function(t, e) {
          if (!r(t)) return t;
          var n, o;
          if (e && "function" == typeof(n = t.toString) && !r(o = n.call(t))) return o;
          if ("function" == typeof(n = t.valueOf) && !r(o = n.call(t))) return o;
          if (!e && "function" == typeof(n = t.toString) && !r(o = n.call(t))) return o;
          throw TypeError("Can't convert object to primitive value")
        }
      },
      function(t, e, n) {
        t.exports = n(4)
      },
      function(t, e, n) {
        "use strict";
        var r = n(52),
          o = n(28),
          i = n(22),
          a = {};
        n(4)(a, n(1)("iterator"), (function() {
          return this
        })),
          t.exports = function(t, e, n) {
            t.prototype = r(a, {
              next: o(1, n)
            }),
              i(t, e + " Iterator")
          }
      },
      function(t, e, n) {
        var r = n(3),
          o = n(53),
          i = n(34),
          a = n(21)("IE_PROTO"),
          c = function() {},
          s = function() {
            var t, e = n(19)("iframe"),
              r = i.length;
            for (e.style.display = "none", n(35).appendChild(e), e.src = "javascript:", (t = e.contentWindow.document).open(), t.write("<script>document.F=Object<\/script>"), t.close(), s = t.F; r--;) delete s.prototype[i[r]];
            return s()
          };
        t.exports = Object.create ||
          function(t, e) {
            var n;
            return null !== t ? (c.prototype = r(t), n = new c, c.prototype = null, n[a] = t) : n = s(),
              void 0 === e ? n: o(n, e)
          }
      },
      function(t, e, n) {
        var r = n(11),
          o = n(3),
          i = n(29);
        t.exports = n(5) ? Object.defineProperties: function(t, e) {
          o(t);
          for (var n, a = i(e), c = a.length, s = 0; c > s;) r.f(t, n = a[s++], e[n]);
          return t
        }
      },
      function(t, e, n) {
        var r = n(12),
          o = n(20),
          i = n(55)(!1),
          a = n(21)("IE_PROTO");
        t.exports = function(t, e) {
          var n, c = o(t),
            s = 0,
            u = [];
          for (n in c) n != a && r(c, n) && u.push(n);
          for (; e.length > s;) r(c, n = e[s++]) && (~i(u, n) || u.push(n));
          return u
        }
      },
      function(t, e, n) {
        var r = n(20),
          o = n(31),
          i = n(56);
        t.exports = function(t) {
          return function(e, n, a) {
            var c, s = r(e),
              u = o(s.length),
              p = i(a, u);
            if (t && n != n) {
              for (; u > p;) if ((c = s[p++]) != c) return ! 0
            } else for (; u > p; p++) if ((t || p in s) && s[p] === n) return t || p || 0;
            return ! t && -1
          }
        }
      },
      function(t, e, n) {
        var r = n(15),
          o = Math.max,
          i = Math.min;
        t.exports = function(t, e) {
          return (t = r(t)) < 0 ? o(t + e, 0) : i(t, e)
        }
      },
      function(t, e, n) {
        var r = n(12),
          o = n(36),
          i = n(21)("IE_PROTO"),
          a = Object.prototype;
        t.exports = Object.getPrototypeOf ||
          function(t) {
            return t = o(t),
              r(t, i) ? t[i] : "function" == typeof t.constructor && t instanceof t.constructor ? t.constructor.prototype: t instanceof Object ? a: null
          }
      },
      function(t, e, n) {
        n(59);
        for (var r = n(0), o = n(4), i = n(8), a = n(1)("toStringTag"), c = "CSSRuleList,CSSStyleDeclaration,CSSValueList,ClientRectList,DOMRectList,DOMStringList,DOMTokenList,DataTransferItemList,FileList,HTMLAllCollection,HTMLCollection,HTMLFormElement,HTMLSelectElement,MediaList,MimeTypeArray,NamedNodeMap,NodeList,PaintRequestList,Plugin,PluginArray,SVGLengthList,SVGNumberList,SVGPathSegList,SVGPointList,SVGStringList,SVGTransformList,SourceBufferList,StyleSheetList,TextTrackCueList,TextTrackList,TouchList".split(","), s = 0; s < c.length; s++) {
          var u = c[s],
            p = r[u],
            f = p && p.prototype;
          f && !f[a] && o(f, a, u),
            i[u] = i.Array
        }
      },
      function(t, e, n) {
        "use strict";
        var r = n(60),
          o = n(61),
          i = n(8),
          a = n(20);
        t.exports = n(27)(Array, "Array", (function(t, e) {
          this._t = a(t),
            this._i = 0,
            this._k = e
        }), (function() {
          var t = this._t,
            e = this._k,
            n = this._i++;
          return ! t || n >= t.length ? (this._t = void 0, o(1)) : o(0, "keys" == e ? n: "values" == e ? t[n] : [n, t[n]])
        }), "values"),
          i.Arguments = i.Array,
          r("keys"),
          r("values"),
          r("entries")
      },
      function(t, e) {
        t.exports = function() {}
      },
      function(t, e) {
        t.exports = function(t, e) {
          return {
            value: e,
            done: !!t
          }
        }
      },
      function(t, e, n) {
        "use strict";
        var r, o, i, a, c = n(17),
          s = n(0),
          u = n(9),
          p = n(37),
          f = n(6),
          h = n(7),
          l = n(10),
          d = n(63),
          g = n(64),
          v = n(38),
          m = n(39).set,
          y = n(69)(),
          x = n(23),
          w = n(40),
          C = n(70),
          b = n(41),
          S = s.TypeError,
          A = s.process,
          k = A && A.versions,
          O = k && k.v8 || "",
          I = s.Promise,
          E = "process" == p(A),
          T = function() {},
          B = o = x.f,
          M = !!
            function() {
              try {
                var t = I.resolve(1),
                  e = (t.constructor = {})[n(1)("species")] = function(t) {
                    t(T, T)
                  };
                return (E || "function" == typeof PromiseRejectionEvent) && t.then(T) instanceof e && 0 !== O.indexOf("6.6") && -1 === C.indexOf("Chrome/66")
              } catch(t) {}
            } (),
          L = function(t) {
            var e;
            return ! (!h(t) || "function" != typeof(e = t.then)) && e
          },
          j = function(t, e) {
            if (!t._n) {
              t._n = !0;
              var n = t._c;
              y((function() {
                for (var r = t._v,
                       o = 1 == t._s,
                       i = 0,
                       a = function(e) {
                         var n, i, a, c = o ? e.ok: e.fail,
                           s = e.resolve,
                           u = e.reject,
                           p = e.domain;
                         try {
                           c ? (o || (2 == t._h && Y(t), t._h = 1), !0 === c ? n = r: (p && p.enter(), n = c(r), p && (p.exit(), a = !0)), n === e.promise ? u(S("Promise-chain cycle")) : (i = L(n)) ? i.call(n, s, u) : s(n)) : u(r)
                         } catch(t) {
                           p && !a && p.exit(),
                             u(t)
                         }
                       }; n.length > i;) a(n[i++]);
                t._c = [],
                  t._n = !1,
                e && !t._h && R(t)
              }))
            }
          },
          R = function(t) {
            m.call(s, (function() {
              var e, n, r, o = t._v,
                i = H(t);
              if (i && (e = w((function() {
                E ? A.emit("unhandledRejection", o, t) : (n = s.onunhandledrejection) ? n({
                  promise: t,
                  reason: o
                }) : (r = s.console) && r.error && r.error("Unhandled promise rejection", o)
              })), t._h = E || H(t) ? 2 : 1), t._a = void 0, i && e.e) throw e.v
            }))
          },
          H = function(t) {
            return 1 !== t._h && 0 === (t._a || t._c).length
          },
          Y = function(t) {
            m.call(s, (function() {
              var e;
              E ? A.emit("rejectionHandled", t) : (e = s.onrejectionhandled) && e({
                promise: t,
                reason: t._v
              })
            }))
          },
          X = function(t) {
            var e = this;
            e._d || (e._d = !0, (e = e._w || e)._v = t, e._s = 2, e._a || (e._a = e._c.slice()), j(e, !0))
          },
          N = function(t) {
            var e, n = this;
            if (!n._d) {
              n._d = !0,
                n = n._w || n;
              try {
                if (n === t) throw S("Promise can't be resolved itself"); (e = L(t)) ? y((function() {
                  var r = {
                    _w: n,
                    _d: !1
                  };
                  try {
                    e.call(t, u(N, r, 1), u(X, r, 1))
                  } catch(t) {
                    X.call(r, t)
                  }
                })) : (n._v = t, n._s = 1, j(n, !1))
              } catch(t) {
                X.call({
                    _w: n,
                    _d: !1
                  },
                  t)
              }
            }
          };
        M || (I = function(t) {
          d(this, I, "Promise", "_h"),
            l(t),
            r.call(this);
          try {
            t(u(N, this, 1), u(X, this, 1))
          } catch(t) {
            X.call(this, t)
          }
        },
          (r = function(t) {
            this._c = [],
              this._a = void 0,
              this._s = 0,
              this._d = !1,
              this._v = void 0,
              this._h = 0,
              this._n = !1
          }).prototype = n(71)(I.prototype, {
            then: function(t, e) {
              var n = B(v(this, I));
              return n.ok = "function" != typeof t || t,
                n.fail = "function" == typeof e && e,
                n.domain = E ? A.domain: void 0,
                this._c.push(n),
              this._a && this._a.push(n),
              this._s && j(this, !1),
                n.promise
            },
            catch: function(t) {
              return this.then(void 0, t)
            }
          }), i = function() {
          var t = new r;
          this.promise = t,
            this.resolve = u(N, t, 1),
            this.reject = u(X, t, 1)
        },
          x.f = B = function(t) {
            return t === I || t === a ? new i(t) : o(t)
          }),
          f(f.G + f.W + f.F * !M, {
            Promise: I
          }),
          n(22)(I, "Promise"),
          n(72)("Promise"),
          a = n(2).Promise,
          f(f.S + f.F * !M, "Promise", {
            reject: function(t) {
              var e = B(this);
              return (0, e.reject)(t),
                e.promise
            }
          }),
          f(f.S + f.F * (c || !M), "Promise", {
            resolve: function(t) {
              return b(c && this === a ? I: this, t)
            }
          }),
          f(f.S + f.F * !(M && n(73)((function(t) {
            I.all(t).
            catch(T)
          }))), "Promise", {
            all: function(t) {
              var e = this,
                n = B(e),
                r = n.resolve,
                o = n.reject,
                i = w((function() {
                  var n = [],
                    i = 0,
                    a = 1;
                  g(t, !1, (function(t) {
                    var c = i++,
                      s = !1;
                    n.push(void 0),
                      a++,
                      e.resolve(t).then((function(t) {
                        s || (s = !0, n[c] = t, --a || r(n))
                      }), o)
                  })),
                  --a || r(n)
                }));
              return i.e && o(i.v),
                n.promise
            },
            race: function(t) {
              var e = this,
                n = B(e),
                r = n.reject,
                o = w((function() {
                  g(t, !1, (function(t) {
                    e.resolve(t).then(n.resolve, r)
                  }))
                }));
              return o.e && r(o.v),
                n.promise
            }
          })
      },
      function(t, e) {
        t.exports = function(t, e, n, r) {
          if (! (t instanceof e) || void 0 !== r && r in t) throw TypeError(n + ": incorrect invocation!");
          return t
        }
      },
      function(t, e, n) {
        var r = n(9),
          o = n(65),
          i = n(66),
          a = n(3),
          c = n(31),
          s = n(67),
          u = {},
          p = {}; (e = t.exports = function(t, e, n, f, h) {
          var l, d, g, v, m = h ?
              function() {
                return t
              }: s(t),
            y = r(n, f, e ? 2 : 1),
            x = 0;
          if ("function" != typeof m) throw TypeError(t + " is not iterable!");
          if (i(m)) {
            for (l = c(t.length); l > x; x++) if ((v = e ? y(a(d = t[x])[0], d[1]) : y(t[x])) === u || v === p) return v
          } else for (g = m.call(t); ! (d = g.next()).done;) if ((v = o(g, y, d.value, e)) === u || v === p) return v
        }).BREAK = u,
          e.RETURN = p
      },
      function(t, e, n) {
        var r = n(3);
        t.exports = function(t, e, n, o) {
          try {
            return o ? e(r(n)[0], n[1]) : e(n)
          } catch(e) {
            var i = t.
              return;
            throw void 0 !== i && r(i.call(t)),
              e
          }
        }
      },
      function(t, e, n) {
        var r = n(8),
          o = n(1)("iterator"),
          i = Array.prototype;
        t.exports = function(t) {
          return void 0 !== t && (r.Array === t || i[o] === t)
        }
      },
      function(t, e, n) {
        var r = n(37),
          o = n(1)("iterator"),
          i = n(8);
        t.exports = n(2).getIteratorMethod = function(t) {
          if (null != t) return t[o] || t["@@iterator"] || i[r(t)]
        }
      },
      function(t, e) {
        t.exports = function(t, e, n) {
          var r = void 0 === n;
          switch (e.length) {
            case 0:
              return r ? t() : t.call(n);
            case 1:
              return r ? t(e[0]) : t.call(n, e[0]);
            case 2:
              return r ? t(e[0], e[1]) : t.call(n, e[0], e[1]);
            case 3:
              return r ? t(e[0], e[1], e[2]) : t.call(n, e[0], e[1], e[2]);
            case 4:
              return r ? t(e[0], e[1], e[2], e[3]) : t.call(n, e[0], e[1], e[2], e[3])
          }
          return t.apply(n, e)
        }
      },
      function(t, e, n) {
        var r = n(0),
          o = n(39).set,
          i = r.MutationObserver || r.WebKitMutationObserver,
          a = r.process,
          c = r.Promise,
          s = "process" == n(13)(a);
        t.exports = function() {
          var t, e, n, u = function() {
            var r, o;
            for (s && (r = a.domain) && r.exit(); t;) {
              o = t.fn,
                t = t.next;
              try {
                o()
              } catch(r) {
                throw t ? n() : e = void 0,
                  r
              }
            }
            e = void 0,
            r && r.enter()
          };
          if (s) n = function() {
            a.nextTick(u)
          };
          else if (!i || r.navigator && r.navigator.standalone) if (c && c.resolve) {
            var p = c.resolve(void 0);
            n = function() {
              p.then(u)
            }
          } else n = function() {
            o.call(r, u)
          };
          else {
            var f = !0,
              h = document.createTextNode("");
            new i(u).observe(h, {
              characterData: !0
            }),
              n = function() {
                h.data = f = !f
              }
          }
          return function(r) {
            var o = {
              fn: r,
              next: void 0
            };
            e && (e.next = o),
            t || (t = o, n()),
              e = o
          }
        }
      },
      function(t, e, n) {
        var r = n(0).navigator;
        t.exports = r && r.userAgent || ""
      },
      function(t, e, n) {
        var r = n(4);
        t.exports = function(t, e, n) {
          for (var o in e) n && t[o] ? t[o] = e[o] : r(t, o, e[o]);
          return t
        }
      },
      function(t, e, n) {
        "use strict";
        var r = n(0),
          o = n(2),
          i = n(11),
          a = n(5),
          c = n(1)("species");
        t.exports = function(t) {
          var e = "function" == typeof o[t] ? o[t] : r[t];
          a && e && !e[c] && i.f(e, c, {
            configurable: !0,
            get: function() {
              return this
            }
          })
        }
      },
      function(t, e, n) {
        var r = n(1)("iterator"),
          o = !1;
        try {
          var i = [7][r]();
          i.
            return = function() {
            o = !0
          },
            Array.from(i, (function() {
              throw 2
            }))
        } catch(t) {}
        t.exports = function(t, e) {
          if (!e && !o) return ! 1;
          var n = !1;
          try {
            var i = [7],
              a = i[r]();
            a.next = function() {
              return {
                done: n = !0
              }
            },
              i[r] = function() {
                return a
              },
              t(i)
          } catch(t) {}
          return n
        }
      },
      function(t, e, n) {
        "use strict";
        var r = n(6),
          o = n(2),
          i = n(0),
          a = n(38),
          c = n(41);
        r(r.P + r.R, "Promise", {
          finally: function(t) {
            var e = a(this, o.Promise || i.Promise),
              n = "function" == typeof t;
            return this.then(n ?
              function(n) {
                return c(e, t()).then((function() {
                  return n
                }))
              }: t, n ?
              function(n) {
                return c(e, t()).then((function() {
                  throw n
                }))
              }: t)
          }
        })
      },
      function(t, e, n) {
        "use strict";
        var r = n(6),
          o = n(23),
          i = n(40);
        r(r.S, "Promise", {
          try: function(t) {
            var e = o.f(this),
              n = i(t);
            return (n.e ? e.reject: e.resolve)(n.v),
              e.promise
          }
        })
      },
      function(t, e, n) {
        var r = function(t) {
          "use strict";
          var e = Object.prototype,
            n = e.hasOwnProperty,
            r = "function" == typeof Symbol ? Symbol: {},
            o = r.iterator || "@@iterator",
            i = r.asyncIterator || "@@asyncIterator",
            a = r.toStringTag || "@@toStringTag";
          function c(t, e, n, r) {
            var o = e && e.prototype instanceof p ? e: p,
              i = Object.create(o.prototype),
              a = new b(r || []);
            return i._invoke = function(t, e, n) {
              var r = "suspendedStart";
              return function(o, i) {
                if ("executing" === r) throw new Error("Generator is already running");
                if ("completed" === r) {
                  if ("throw" === o) throw i;
                  return A()
                }
                for (n.method = o, n.arg = i;;) {
                  var a = n.delegate;
                  if (a) {
                    var c = x(a, n);
                    if (c) {
                      if (c === u) continue;
                      return c
                    }
                  }
                  if ("next" === n.method) n.sent = n._sent = n.arg;
                  else if ("throw" === n.method) {
                    if ("suspendedStart" === r) throw r = "completed",
                      n.arg;
                    n.dispatchException(n.arg)
                  } else "return" === n.method && n.abrupt("return", n.arg);
                  r = "executing";
                  var p = s(t, e, n);
                  if ("normal" === p.type) {
                    if (r = n.done ? "completed": "suspendedYield", p.arg === u) continue;
                    return {
                      value: p.arg,
                      done: n.done
                    }
                  }
                  "throw" === p.type && (r = "completed", n.method = "throw", n.arg = p.arg)
                }
              }
            } (t, n, a),
              i
          }
          function s(t, e, n) {
            try {
              return {
                type: "normal",
                arg: t.call(e, n)
              }
            } catch(t) {
              return {
                type: "throw",
                arg: t
              }
            }
          }
          t.wrap = c;
          var u = {};
          function p() {}
          function f() {}
          function h() {}
          var l = {};
          l[o] = function() {
            return this
          };
          var d = Object.getPrototypeOf,
            g = d && d(d(S([])));
          g && g !== e && n.call(g, o) && (l = g);
          var v = h.prototype = p.prototype = Object.create(l);
          function m(t) { ["next", "throw", "return"].forEach((function(e) {
            t[e] = function(t) {
              return this._invoke(e, t)
            }
          }))
          }
          function y(t, e) {
            var r;
            this._invoke = function(o, i) {
              function a() {
                return new e((function(r, a) { !
                  function r(o, i, a, c) {
                    var u = s(t[o], t, i);
                    if ("throw" !== u.type) {
                      var p = u.arg,
                        f = p.value;
                      return f && "object" == typeof f && n.call(f, "__await") ? e.resolve(f.__await).then((function(t) {
                        r("next", t, a, c)
                      }), (function(t) {
                        r("throw", t, a, c)
                      })) : e.resolve(f).then((function(t) {
                        p.value = t,
                          a(p)
                      }), (function(t) {
                        return r("throw", t, a, c)
                      }))
                    }
                    c(u.arg)
                  } (o, i, r, a)
                }))
              }
              return r = r ? r.then(a, a) : a()
            }
          }
          function x(t, e) {
            var n = t.iterator[e.method];
            if (void 0 === n) {
              if (e.delegate = null, "throw" === e.method) {
                if (t.iterator.
                  return && (e.method = "return", e.arg = void 0, x(t, e), "throw" === e.method)) return u;
                e.method = "throw",
                  e.arg = new TypeError("The iterator does not provide a 'throw' method")
              }
              return u
            }
            var r = s(n, t.iterator, e.arg);
            if ("throw" === r.type) return e.method = "throw",
              e.arg = r.arg,
              e.delegate = null,
              u;
            var o = r.arg;
            return o ? o.done ? (e[t.resultName] = o.value, e.next = t.nextLoc, "return" !== e.method && (e.method = "next", e.arg = void 0), e.delegate = null, u) : o: (e.method = "throw", e.arg = new TypeError("iterator result is not an object"), e.delegate = null, u)
          }
          function w(t) {
            var e = {
              tryLoc: t[0]
            };
            1 in t && (e.catchLoc = t[1]),
            2 in t && (e.finallyLoc = t[2], e.afterLoc = t[3]),
              this.tryEntries.push(e)
          }
          function C(t) {
            var e = t.completion || {};
            e.type = "normal",
              delete e.arg,
              t.completion = e
          }
          function b(t) {
            this.tryEntries = [{
              tryLoc: "root"
            }],
              t.forEach(w, this),
              this.reset(!0)
          }
          function S(t) {
            if (t) {
              var e = t[o];
              if (e) return e.call(t);
              if ("function" == typeof t.next) return t;
              if (!isNaN(t.length)) {
                var r = -1,
                  i = function e() {
                    for (; ++r < t.length;) if (n.call(t, r)) return e.value = t[r],
                      e.done = !1,
                      e;
                    return e.value = void 0,
                      e.done = !0,
                      e
                  };
                return i.next = i
              }
            }
            return {
              next: A
            }
          }
          function A() {
            return {
              value: void 0,
              done: !0
            }
          }
          return f.prototype = v.constructor = h,
            h.constructor = f,
            h[a] = f.displayName = "GeneratorFunction",
            t.isGeneratorFunction = function(t) {
              var e = "function" == typeof t && t.constructor;
              return !! e && (e === f || "GeneratorFunction" === (e.displayName || e.name))
            },
            t.mark = function(t) {
              return Object.setPrototypeOf ? Object.setPrototypeOf(t, h) : (t.__proto__ = h, a in t || (t[a] = "GeneratorFunction")),
                t.prototype = Object.create(v),
                t
            },
            t.awrap = function(t) {
              return {
                __await: t
              }
            },
            m(y.prototype),
            y.prototype[i] = function() {
              return this
            },
            t.AsyncIterator = y,
            t.async = function(e, n, r, o, i) {
              void 0 === i && (i = Promise);
              var a = new y(c(e, n, r, o), i);
              return t.isGeneratorFunction(n) ? a: a.next().then((function(t) {
                return t.done ? t.value: a.next()
              }))
            },
            m(v),
            v[a] = "Generator",
            v[o] = function() {
              return this
            },
            v.toString = function() {
              return "[object Generator]"
            },
            t.keys = function(t) {
              var e = [];
              for (var n in t) e.push(n);
              return e.reverse(),
                function n() {
                  for (; e.length;) {
                    var r = e.pop();
                    if (r in t) return n.value = r,
                      n.done = !1,
                      n
                  }
                  return n.done = !0,
                    n
                }
            },
            t.values = S,
            b.prototype = {
              constructor: b,
              reset: function(t) {
                if (this.prev = 0, this.next = 0, this.sent = this._sent = void 0, this.done = !1, this.delegate = null, this.method = "next", this.arg = void 0, this.tryEntries.forEach(C), !t) for (var e in this)"t" === e.charAt(0) && n.call(this, e) && !isNaN( + e.slice(1)) && (this[e] = void 0)
              },
              stop: function() {
                this.done = !0;
                var t = this.tryEntries[0].completion;
                if ("throw" === t.type) throw t.arg;
                return this.rval
              },
              dispatchException: function(t) {
                if (this.done) throw t;
                var e = this;
                function r(n, r) {
                  return a.type = "throw",
                    a.arg = t,
                    e.next = n,
                  r && (e.method = "next", e.arg = void 0),
                    !!r
                }
                for (var o = this.tryEntries.length - 1; o >= 0; --o) {
                  var i = this.tryEntries[o],
                    a = i.completion;
                  if ("root" === i.tryLoc) return r("end");
                  if (i.tryLoc <= this.prev) {
                    var c = n.call(i, "catchLoc"),
                      s = n.call(i, "finallyLoc");
                    if (c && s) {
                      if (this.prev < i.catchLoc) return r(i.catchLoc, !0);
                      if (this.prev < i.finallyLoc) return r(i.finallyLoc)
                    } else if (c) {
                      if (this.prev < i.catchLoc) return r(i.catchLoc, !0)
                    } else {
                      if (!s) throw new Error("try statement without catch or finally");
                      if (this.prev < i.finallyLoc) return r(i.finallyLoc)
                    }
                  }
                }
              },
              abrupt: function(t, e) {
                for (var r = this.tryEntries.length - 1; r >= 0; --r) {
                  var o = this.tryEntries[r];
                  if (o.tryLoc <= this.prev && n.call(o, "finallyLoc") && this.prev < o.finallyLoc) {
                    var i = o;
                    break
                  }
                }
                i && ("break" === t || "continue" === t) && i.tryLoc <= e && e <= i.finallyLoc && (i = null);
                var a = i ? i.completion: {};
                return a.type = t,
                  a.arg = e,
                  i ? (this.method = "next", this.next = i.finallyLoc, u) : this.complete(a)
              },
              complete: function(t, e) {
                if ("throw" === t.type) throw t.arg;
                return "break" === t.type || "continue" === t.type ? this.next = t.arg: "return" === t.type ? (this.rval = this.arg = t.arg, this.method = "return", this.next = "end") : "normal" === t.type && e && (this.next = e),
                  u
              },
              finish: function(t) {
                for (var e = this.tryEntries.length - 1; e >= 0; --e) {
                  var n = this.tryEntries[e];
                  if (n.finallyLoc === t) return this.complete(n.completion, n.afterLoc),
                    C(n),
                    u
                }
              },
              catch: function(t) {
                for (var e = this.tryEntries.length - 1; e >= 0; --e) {
                  var n = this.tryEntries[e];
                  if (n.tryLoc === t) {
                    var r = n.completion;
                    if ("throw" === r.type) {
                      var o = r.arg;
                      C(n)
                    }
                    return o
                  }
                }
                throw new Error("illegal catch attempt")
              },
              delegateYield: function(t, e, n) {
                return this.delegate = {
                  iterator: S(t),
                  resultName: e,
                  nextLoc: n
                },
                "next" === this.method && (this.arg = void 0),
                  u
              }
            },
            t
        } (t.exports);
        try {
          regeneratorRuntime = r
        } catch(t) {
          Function("r", "regeneratorRuntime = r")(r)
        }
      },
      function(t, e, n) {
        n(78),
          t.exports = n(2).Object.assign
      },
      function(t, e, n) {
        var r = n(6);
        r(r.S + r.F, "Object", {
          assign: n(79)
        })
      },
      function(t, e, n) {
        "use strict";
        var r = n(5),
          o = n(29),
          i = n(80),
          a = n(81),
          c = n(36),
          s = n(30),
          u = Object.assign;
        t.exports = !u || n(18)((function() {
          var t = {},
            e = {},
            n = Symbol(),
            r = "abcdefghijklmnopqrst";
          return t[n] = 7,
            r.split("").forEach((function(t) {
              e[t] = t
            })),
          7 != u({},
            t)[n] || Object.keys(u({},
            e)).join("") != r
        })) ?
          function(t, e) {
            for (var n = c(t), u = arguments.length, p = 1, f = i.f, h = a.f; u > p;) for (var l, d = s(arguments[p++]), g = f ? o(d).concat(f(d)) : o(d), v = g.length, m = 0; v > m;) l = g[m++],
            r && !h.call(d, l) || (n[l] = d[l]);
            return n
          }: u
      },
      function(t, e) {
        e.f = Object.getOwnPropertySymbols
      },
      function(t, e) {
        e.f = {}.propertyIsEnumerable
      },
      function(t, e, n) {
        "use strict";
        var r = n(14);
        n.n(r).a
      },
      function(t, e, n) { (e = n(84)(!1)).push([t.i, '\n.upbtn[data-v-048798d4] {\r\n  width: 100%;\r\n  height: 100%;\n}\n.bg[data-v-048798d4] {\r\n  position: fixed;\r\n  top: 0;\r\n  height: 100vh;\r\n  width: 100%;\r\n  background-color: #000;\r\n  left: 0;\r\n  z-index: 521;\n}\n.btn[data-v-048798d4] {\r\n  height: 8vw;\r\n  padding: 0;\r\n  line-height: 8vw;\r\n  font-size: 4vw;\r\n  padding: 0 3.5vw;\r\n  /* width: 16vw; */\r\n  border-radius: 1.333vw;\r\n  text-align: center;\r\n  /* background-color: #ed594c; */\r\n  /* margin-top: 2.667vw; */\n}\n.btn1[data-v-048798d4] {\r\n  height: 8vw;\r\n  line-height: 8vw;\r\n  font-size: 4vw;\r\n  padding: 0 4vw;\r\n  /* width: 16vw; */\r\n  border-radius: 1.333vw;\r\n  text-align: center;\r\n  background-color: #5b6e96;\r\n  /* margin-top: 2.667vw; */\n}\n.img[data-v-048798d4] {\r\n  height: 8vw;\r\n  width: 8vw;\r\n  position: absolute;\r\n  left: calc(50% - 4vw);\r\n  /* top: 2.667vw; */\r\n  background-image: url("data:image/png;base64,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");\r\n  background-size: 100% 100%;\n}' +
      '\n.btndiv[data-v-048798d4] {\r\n  height: 13.333vw;\r\n  color: #fff;\r\n  justify-content: space-between;\r\n  display: flex;\r\n  align-items: center;\r\n  padding: 0 4vw;\r\n  line-height: 13.333vw;\r\n   bottom: 10vh;\r\n    font-size: 4vw;\r\n  position: relative;\n}\n.wrapper[data-v-048798d4] .crop-point {\r\n  opacity: 0;\r\n  z-index: 523;\n}\n.wrapper[data-v-048798d4] .cropper-view-box {\r\n  outline: 1px solid #fff;\r\n  border: 1px solid #fff;\n}\n.wrapper[data-v-048798d4] .vue-cropper {\r\n  background-color: #000;\r\n  background-image: none;\n}\n.wrapper[data-v-048798d4] {\r\n  height: calc(100vh - 21.333vw);\r\n  padding: 4vw;\n}\r\n', ""]),
        t.exports = e
      },
      function(t, e, n) {
        "use strict";
        t.exports = function(t) {
          var e = [];
          return e.toString = function() {
            return this.map((function(e) {
              var n = function(t, e) {
                var n = t[1] || "",
                  r = t[3];
                if (!r) return n;
                if (e && "function" == typeof btoa) {
                  var o = (a = r, c = btoa(unescape(encodeURIComponent(JSON.stringify(a)))), s = "sourceMappingURL=data:application/json;charset=utf-8;base64,".concat(c), "/*# ".concat(s, " */")),
                    i = r.sources.map((function(t) {
                      return "/*# sourceURL=".concat(r.sourceRoot || "").concat(t, " */")
                    }));
                  return [n].concat(i).concat([o]).join("\n")
                }
                var a, c, s;
                return [n].join("\n")
              } (e, t);
              return e[2] ? "@media ".concat(e[2], " {").concat(n, "}") : n
            })).join("")
          },
            e.i = function(t, n, r) {
              "string" == typeof t && (t = [[null, t, ""]]);
              var o = {};
              if (r) for (var i = 0; i < this.length; i++) {
                var a = this[i][0];
                null != a && (o[a] = !0)
              }
              for (var c = 0; c < t.length; c++) {
                var s = [].concat(t[c]);
                r && o[s[0]] || (n && (s[2] ? s[2] = "".concat(n, " and ").concat(s[2]) : s[2] = n), e.push(s))
              }
            },
            e
        }
      },
      function(t, e, n) {
        "use strict";
        n.r(e);
        var r = n(24),
          o = n.n(r),
          i = n(25),
          a = n.n(i),
          c = n(42),
          s = n.n(c),
          u = n(26),
          p = n.n(u),
          f = {
            name: "H5Cropper",
            components: {
              VueCropper: n(43).VueCropper
            },
            props: {
              hideInput: {
                type: Boolean,
                default:
                  !1
              },
              option: {
                type: Object,
                default:
                  function() {
                    return {}
                  }
              }
            },
            data: function() {
              return {
                img: "",
                config: {}
              }
            },
            watch: {
              option: {
                handler: function() {
                  delete this.option.autoCrop,
                  "string" == typeof this.option.outputType && -1 === ["jpeg", "png", "webp"].indexOf(this.option.outputType) && delete this.option.outputType,
                    this.config = p()(this.config, this.option)
                },
                deep: !0
              }
            },
            created: function() {
              delete this.option.autoCrop,
              "string" == typeof this.option.outputType && -1 === ["jpg","jpeg", "png", "webp"].indexOf(this.option.outputType) && delete this.option.outputType,
                this.config = p()({
                    ceilbutton: !1,
                    outputSize: 1,
                    outputType: "png",
                    info: !1,
                    canScale: !0,
                    autoCrop: !1,
                    autoCropWidth: 0,
                    autoCropHeight: 0,
                    fixed: !0,
                    fixedNumber: [1, 1],
                    full: !1,
                    fixedBox: !0,
                    canMove: !0,
                    canMoveBox: !1,
                    original: !1,
                    centerBox: !0,
                    high: !0,
                    infoTrue: !1,
                    maxImgSize: 2e3,
                    enlarge: 1,
                    mode: "100%",
                    cancelButtonText: "Cancel",
                    confirmButtonText: "OK",
                    cancelButtonBackgroundColor: "#606266",
                    confirmButtonBackgroundColor: "#ed594c",
                    cancelButtonTextColor: "#ffffff",
                    confirmButtonTextColor: "#ffffff"
                  },
                  this.option)
            },
            methods: {
              canceltailor: function() {
                this.img = "",
                  this.$emit("canceltailor")
              },
              upphoto: function(t) {
                var e = this;
                return s()(a.a.mark((function n() {
                  var r;
                  return a.a.wrap((function(n) {
                    for (;;) switch (n.prev = n.next) {
                      case 0:
                        if (r = t.target.files[0], e.$refs.headinput.value = null, null == r) {
                          n.next = 9;
                          break
                        }
                        return e.$emit("imgorigoinf", r),
                          n.next = 6,
                          e.onloadimg(r);
                      case 6:
                        e.img = n.sent,
                          e.config.autoCrop = !0,
                          setTimeout((function() {
                            e.addsolide()
                          }), 10);
                      case 9:
                      case "end":
                        return n.stop()
                    }
                  }), n)
                })))()
              },
              onloadimg: function(t) {
                return new o.a((function(e, n) {
                  var r = new FileReader;
                  r.readAsDataURL(t),
                    r.onload = function(t) {
                      e(t.target.result)
                    }
                }))
              },
              tailoring: function() {
                var t = this;
                this.$refs.cropper.getCropData((function(e) {
                  t.$emit("getbase64Data", e),
                    t.$emit("getbase64", e),
                    t.img = "",
                    t.config.autoCrop = !1

                })),
                  this.$refs.cropper.getCropBlob((function(e) {
                    t.$emit("getblobData", e),
                      t.$emit("getblob", e);
                    var n = {
                        jpeg: "jpg",
                        png: "png",
                        webp: "webp"
                      } [t.config.outputType],
                      r = (new Date).getTime(),
                      o = new File([e], "".concat(r, ".").concat(n), {
                        type: "image/".concat(t.config.outputType)
                      });
                    t.$emit("getFile", o),
                      t.$emit("get-file", o),
                      t.img = "",
                      t.config.autoCrop = !1
                  }))
              },
              rotating: function() {
                this.$refs.cropper.rotateRight(),
                  document.getElementsByClassName("cropper-modal")[0].style = "background-color: rgba(0,0,0,0.5);transition: 0.88s"
              },
              moving: function(t) {
                t.moving ? document.getElementsByClassName("cropper-modal")[0].style = "background-color: rgba(0,0,0,0.5);transition: 0.88s": document.getElementsByClassName("cropper-modal")[0].style = "background-color: rgba(0,0,0,0.8);transition: 0.88s"
              },
              addsolide: function() {
                if (null == document.getElementById("vertical")) {
                  var t = document.getElementsByClassName("cropper-crop-box")[0],
                    e = document.createElement("div");
                  e.id = "vertical",
                    e.style.width = "1px",
                    e.style.height = "100%",
                    e.style.top = "0px",
                    e.style.left = "33%",
                    e.style.position = "absolute",
                    e.style.backgroundColor = "#fff",
                    e.style.zIndex = "522",
                    e.style.opacity = "0.5";
                  var n = document.createElement("div");
                  n.style.width = "1px",
                    n.style.height = "100%",
                    n.style.top = "0px",
                    n.style.right = "33%",
                    n.style.position = "absolute",
                    n.style.backgroundColor = "#fff",
                    n.style.zIndex = "522",
                    n.style.opacity = "0.5";
                  var r = document.createElement("div");
                  r.style.width = "100%",
                    r.style.height = "1px",
                    r.style.top = "33%",
                    r.style.left = "0px",
                    r.style.position = "absolute",
                    r.style.backgroundColor = "#fff",
                    r.style.zIndex = "522",
                    r.style.opacity = "0.5";
                  var o = document.createElement("div");
                  o.style.width = "100%",
                    o.style.height = "1px",
                    o.style.bottom = "33%",
                    o.style.left = "0px",
                    o.style.position = "absolute",
                    o.style.backgroundColor = "#fff",
                    o.style.zIndex = "522",
                    o.style.opacity = "0.5";
                  var i = document.createElement("div");
                  i.style.width = "30px",
                    i.style.height = "4px",
                    i.style.top = "-4px",
                    i.style.left = "-4px",
                    i.style.position = "absolute",
                    i.style.backgroundColor = "#fff",
                    i.style.zIndex = "522",
                    i.style.opacity = "1";
                  var a = document.createElement("div");
                  a.style.width = "4px",
                    a.style.height = "30px",
                    a.style.top = "-4px",
                    a.style.left = "-4px",
                    a.style.position = "absolute",
                    a.style.backgroundColor = "#fff",
                    a.style.zIndex = "522",
                    a.style.opacity = "1";
                  var c = document.createElement("div");
                  c.style.width = "30px",
                    c.style.height = "4px",
                    c.style.top = "-4px",
                    c.style.right = "-4px",
                    c.style.position = "absolute",
                    c.style.backgroundColor = "#fff",
                    c.style.zIndex = "522",
                    c.style.opacity = "1";
                  var s = document.createElement("div");
                  s.style.width = "4px",
                    s.style.height = "30px",
                    s.style.top = "-4px",
                    s.style.right = "-4px",
                    s.style.position = "absolute",
                    s.style.backgroundColor = "#fff",
                    s.style.zIndex = "522",
                    s.style.opacity = "1";
                  var u = document.createElement("div");
                  u.style.width = "30px",
                    u.style.height = "4px",
                    u.style.bottom = "-4px",
                    u.style.left = "-4px",
                    u.style.position = "absolute",
                    u.style.backgroundColor = "#fff",
                    u.style.zIndex = "522",
                    u.style.opacity = "1";
                  var p = document.createElement("div");
                  p.style.width = "4px",
                    p.style.height = "30px",
                    p.style.bottom = "-4px",
                    p.style.left = "-4px",
                    p.style.position = "absolute",
                    p.style.backgroundColor = "#fff",
                    p.style.zIndex = "522",
                    p.style.opacity = "1";
                  var f = document.createElement("div");
                  f.style.width = "30px",
                    f.style.height = "4px",
                    f.style.bottom = "-4px",
                    f.style.right = "-4px",
                    f.style.position = "absolute",
                    f.style.backgroundColor = "#fff",
                    f.style.zIndex = "522",
                    f.style.opacity = "1";
                  var h = document.createElement("div");
                  h.style.width = "4px",
                    h.style.height = "30px",
                    h.style.bottom = "-4px",
                    h.style.right = "-4px",
                    h.style.position = "absolute",
                    h.style.backgroundColor = "#fff",
                    h.style.zIndex = "522",
                    h.style.opacity = "1",
                    t.appendChild(e),
                    t.appendChild(n),
                    t.appendChild(r),
                    t.appendChild(o),
                    t.appendChild(i),
                    t.appendChild(a),
                    t.appendChild(c),
                    t.appendChild(s),
                    t.appendChild(u),
                    t.appendChild(p),
                    t.appendChild(f),
                    t.appendChild(h)
                }
              },
              loadFile: function(t) {
                var e = this;
                if (! (t instanceof File)) throw new Error("Arguments file is not File");
                this.onloadimg(t).then((function(t) {
                  e.img = t,
                    setTimeout((function() {
                      e.config.autoCrop = !0,
                        e.addsolide()
                    }), 10)
                }))
              },
              loadBase64: function(t) {
                var e = this;
                if ("string" != typeof t) throw new Error("Arguments base64 is not string");
                var n = t.split(",");
                if (!/^data:image\/(.*?);base64$/.test(n[0])) throw new Error("Arguments base64 MIME is not image/*");
                if (!/^[\/]?([\da-zA-Z]+[\/+]+)*[\da-zA-Z]+([+=]{1,2}|[\/])?$/.test(n[1])) throw new Error("Not standard base64");
                this.img = t,
                  setTimeout((function() {
                    e.config.autoCrop = !0,
                      e.addsolide()
                  }), 10)
              }
            }
          };
        n(82);
        var h = function(t, e, n, r, o, i, a, c) {
          var s, u = "function" == typeof t ? t.options: t;
          if (e && (u.render = e, u.staticRenderFns = n, u._compiled = !0), r && (u.functional = !0), i && (u._scopeId = "data-v-" + i), a ? (s = function(t) { (t = t || this.$vnode && this.$vnode.ssrContext || this.parent && this.parent.$vnode && this.parent.$vnode.ssrContext) || "undefined" == typeof __VUE_SSR_CONTEXT__ || (t = __VUE_SSR_CONTEXT__),
          o && o.call(this, t),
          t && t._registeredComponents && t._registeredComponents.add(a)
          },
            u._ssrRegister = s) : o && (s = c ?
            function() {
              o.call(this, this.$root.$options.shadowRoot)
            }: o), s) if (u.functional) {
            u._injectStyles = s;
            var p = u.render;
            u.render = function(t, e) {
              return s.call(e),
                p(t, e)
            }
          } else {
            var f = u.beforeCreate;
            u.beforeCreate = f ? [].concat(f, s) : [s]
          }
          return {
            exports: t,
            options: u
          }
        } (f, (function() {
          var t = this,
            e = t.$createElement,
            n = t._self._c || e;
          return n("div", {
              staticClass: "upbtn"
            },
            [t.hideInput ? t._e() : n("input", {
              ref: "headinput",
              staticClass: "upbtn",
              staticStyle: {
                opacity: "0"
              },
              attrs: {
                type: "file",
                accept: "image/*"
              },
              on: {
                change: function(e) {
                  return t.upphoto(e)
                }
              }
            }), t._v(" "), "" != t.img ? n("div", {
                staticClass: "bg"
              },
              [t.config.ceilbutton ? n("div", {
                  staticClass: "btndiv"
                },
                [n("div", {
                    staticClass: "btn",
                    style: "backgroundColor:" + t.config.cancelButtonBackgroundColor + ";color:" + t.config.cancelButtonTextColor,
                    on: {
                      click: t.canceltailor
                    }
                  },
                  [t._v(t._s(t.config.cancelButtonText))]), t._v(" "), n("div", {
                  staticClass: "img",
                  on: {
                    click: t.rotating
                  }
                }), t._v(" "), n("div", {
                    staticClass: "btn",
                    style: "backgroundColor:" + t.config.confirmButtonBackgroundColor + ";color:" + t.config.confirmButtonTextColor,
                    on: {
                      click: t.tailoring
                    }
                  },
                  [t._v(t._s(t.config.confirmButtonText))])]) : t._e(), t._v(" "), n("div", {
                  staticClass: "wrapper"
                },
                [n("vueCropper", {
                  ref: "cropper",
                  attrs: {
                    id: "cropper",
                    img: t.img,
                    outputSize: t.config.outputSize,
                    outputType: t.config.outputType,
                    info: t.config.info,
                    canScale: t.config.canScale,
                    autoCrop: t.config.autoCrop,
                    autoCropWidth: t.config.autoCropWidth,
                    autoCropHeight: t.config.autoCropHeight,
                    fixed: t.config.fixed,
                    fixedNumber: t.config.fixedNumber,
                    full: t.config.full,
                    fixedBox: t.config.fixedBox,
                    canMove: t.config.canMove,
                    canMoveBox: t.config.canMoveBox,
                    original: t.config.original,
                    centerBox: t.config.centerBox,
                    high: t.config.high,
                    infoTrue: t.config.infoTrue,
                    maxImgSize: t.config.maxImgSize,
                    enlarge: t.config.enlarge,
                    mode: t.config.mode
                  },
                  on: {
                    cropMoving: function(e) {
                      return t.moving(e)
                    },
                    imgMoving: function(e) {
                      return t.moving(e)
                    }
                  }
                })], 1), t._v(" "), t.config.ceilbutton ? t._e() : n("div", {
                  staticClass: "btndiv"
                },
                [n("div", {
                    staticClass: "btn",
                    style: "backgroundColor:" + t.config.cancelButtonBackgroundColor + ";color:" + t.config.cancelButtonTextColor,
                    on: {
                      click: t.canceltailor
                    }
                  },
                  [t._v(t._s(t.config.cancelButtonText))]), t._v(" "), n("div", {
                  staticClass: "img",
                  on: {
                    click: t.rotating
                  }
                }), t._v(" "), n("div", {
                    staticClass: "btn",
                    style: "backgroundColor:" + t.config.confirmButtonBackgroundColor + ";color:" + t.config.confirmButtonTextColor,
                    on: {
                      click: t.tailoring
                    }
                  },
                  [t._v(t._s(t.config.confirmButtonText))])])]) : t._e()])
        }), [], !1, null, "048798d4", null).exports;
        h.install = function(t) {
          return t.component("H5Cropper", h)
        };
        "undefined" != typeof window && window.Vue &&
        function(t) {
          arguments.length > 1 && void 0 !== arguments[1] && arguments[1];
          t.component("H5Cropper", h)
        } (window.Vue);
        e.
          default = h
      },
      function(t, e, n) {
        "use strict";
        function r(t, e) {
          for (var n = [], r = {},
                 o = 0; o < e.length; o++) {
            var i = e[o],
              a = i[0],
              c = {
                id: t + ":" + o,
                css: i[1],
                media: i[2],
                sourceMap: i[3]
              };
            r[a] ? r[a].parts.push(c) : n.push(r[a] = {
              id: a,
              parts: [c]
            })
          }
          return n
        }
        n.r(e),
          n.d(e, "default", (function() {
            return l
          }));
        var o = "undefined" != typeof document;
        if ("undefined" != typeof DEBUG && DEBUG && !o) throw new Error("vue-style-loader cannot be used in a non-browser environment. Use { target: 'node' } in your Webpack config to indicate a server-rendering environment.");
        var i = {},
          a = o && (document.head || document.getElementsByTagName("head")[0]),
          c = null,
          s = 0,
          u = !1,
          p = function() {},
          f = null,
          h = "undefined" != typeof navigator && /msie [6-9]\b/.test(navigator.userAgent.toLowerCase());
        function l(t, e, n, o) {
          u = n,
            f = o || {};
          var a = r(t, e);
          return d(a),
            function(e) {
              for (var n = [], o = 0; o < a.length; o++) {
                var c = a[o]; (s = i[c.id]).refs--,
                  n.push(s)
              }
              e ? d(a = r(t, e)) : a = [];
              for (o = 0; o < n.length; o++) {
                var s;
                if (0 === (s = n[o]).refs) {
                  for (var u = 0; u < s.parts.length; u++) s.parts[u]();
                  delete i[s.id]
                }
              }
            }
        }
        function d(t) {
          for (var e = 0; e < t.length; e++) {
            var n = t[e],
              r = i[n.id];
            if (r) {
              r.refs++;
              for (var o = 0; o < r.parts.length; o++) r.parts[o](n.parts[o]);
              for (; o < n.parts.length; o++) r.parts.push(v(n.parts[o]));
              r.parts.length > n.parts.length && (r.parts.length = n.parts.length)
            } else {
              var a = [];
              for (o = 0; o < n.parts.length; o++) a.push(v(n.parts[o]));
              i[n.id] = {
                id: n.id,
                refs: 1,
                parts: a
              }
            }
          }
        }
        function g() {
          var t = document.createElement("style");
          return t.type = "text/css",
            a.appendChild(t),
            t
        }
        function v(t) {
          var e, n, r = document.querySelector('style[data-vue-ssr-id~="' + t.id + '"]');
          if (r) {
            if (u) return p;
            r.parentNode.removeChild(r)
          }
          if (h) {
            var o = s++;
            r = c || (c = g()),
              e = x.bind(null, r, o, !1),
              n = x.bind(null, r, o, !0)
          } else r = g(),
            e = w.bind(null, r),
            n = function() {
              r.parentNode.removeChild(r)
            };
          return e(t),
            function(r) {
              if (r) {
                if (r.css === t.css && r.media === t.media && r.sourceMap === t.sourceMap) return;
                e(t = r)
              } else n()
            }
        }
        var m, y = (m = [],
          function(t, e) {
            return m[t] = e,
              m.filter(Boolean).join("\n")
          });
        function x(t, e, n, r) {
          var o = n ? "": r.css;
          if (t.styleSheet) t.styleSheet.cssText = y(e, o);
          else {
            var i = document.createTextNode(o),
              a = t.childNodes;
            a[e] && t.removeChild(a[e]),
              a.length ? t.insertBefore(i, a[e]) : t.appendChild(i)
          }
        }
        function w(t, e) {
          var n = e.css,
            r = e.media,
            o = e.sourceMap;
          if (r && t.setAttribute("media", r), f.ssrId && t.setAttribute("data-vue-ssr-id", e.id), o && (n += "\n/*# sourceURL=" + o.sources[0] + " */", n += "\n/*# sourceMappingURL=data:application/json;base64," + btoa(unescape(encodeURIComponent(JSON.stringify(o)))) + " */"), t.styleSheet) t.styleSheet.cssText = n;
          else {
            for (; t.firstChild;) t.removeChild(t.firstChild);
            t.appendChild(document.createTextNode(n))
          }
        }
      }])
  }));
