<!doctype html>
<title>Site Maintenance</title>
<style>
  body { text-align: center; padding: 150px; }
  h1 { font-size: 50px; }
  body { font: 20px Helvetica, sans-serif; color: #fff; }
  article { display: block; text-align: left; width: 650px; margin: 0 auto; }
  a { color: #dc8100; text-decoration: none; }
  a:hover { color: #333; text-decoration: none; }
</style>

<article>
    <h1>We&rsquo;ll be back soon!</h1>
    <div>
        <p>Sorry for the inconvenience but we&rsquo;re performing some maintenance at the moment. If you need to you can always <a href="mailto:#">contact us</a>, otherwise we&rsquo;ll be back online shortly!</p>
        <p>&mdash; Ubonus.com Team Version 1.0</p>
    </div>
</article>



<div class="system">
  <div class="sun"></div>
  <div class="mer-path"></div>
    <div class="mer"></div>
  <div class="ven-path"></div>
  <div class="ven"></div>
  <div class="ear-path"></div>
  <div class="ear"><div class="lune"></div></div>
  <div class="mar-path"></div>
  <div class="mar">
    <div class="pho"></div>
    <div class="dem"></div>
  </div>
  <div class="jup-path"></div>
  <div class="jup">
    <div class="spot"></div>
    <div class="jove io"></div>
    <div class="jove eur"></div>
    <div class="jove gan"></div>
    <div class="jove cal"></div>
  </div>
  <div class="sat-path"></div>
  <div class="sat">
    <div class="f-ring"></div>
    <div class="a-ring"></div>
    <div class="b-ring"></div>
    <div class="c-ring"></div>
  </div>
  <div class="ura-path"></div>
  <div class="ura">
    <div class="e-ring"></div>
  </div>
  <div class="nep-path"></div>
  <div class="nep">
    <div class="spot"></div>
  </div>
  <div class="plu-path"></div>
  <div class="plu"></div>
</div>


<style type="text/css">
  /**
 * I was out the other evening looking at Venus with the setting sun and thought, I wonder where all the planets
 * are in relation to each other right now. I knew what an Orrery was, but I'd never built one. So, given my mate
 * Donovan's (@donovanh: http://cssanimation.rocks/) penchant for CSS animation, I thought I'd give it a go 
 * building one in pure CSS.
 * 
 * Many thanks to @aidandore and @iandevlin too for suggestions and improvements
 *
 * Chin up Pluto. You'll always be a planet to me... 
 *
 * Tady: http://tady.me
 * @tadywankenobi
 */
/**
 * Move in a circle without wrapper elements
 * Idea by Aryeh Gregor, simplified by Lea Verou, borrowed by me!
 */
body {
  background-color: #012;
  background-image: url("https://cssanimation.rocks/starwars/images/bg.jpg");
  background-size: 33%;
  background-repeat: repeat;
  min-height: 2025px;
}

.system {
  position: relative;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  -webkit-transform: scale(0.75);
  transform: scale(0.75);
}

.sun {
  width: 144px;
  height: 144px;
  border-radius: 72px;
  position: absolute;
  top: 1066.6666666667px;
  left: 50%;
  margin: -72px;
  background-image: url("http://sdo.gsfc.nasa.gov/assets/img/latest/latest_256_HMIIF.jpg");
  background-size: 144px;
  background-repeat: no-repeat;
}

@-webkit-keyframes rot-mer {
  from {
    -webkit-transform: rotate(0deg) translatey(-84px) rotate(0deg);
  }
  to {
    -webkit-transform: rotate(360deg) translatey(-84px) rotate(-360deg);
  }
}
@-keyframes rot-mer {
  from {
    -webkit-transform: rotate(0deg) translatey(-84px) rotate(0deg);
            transform: rotate(0deg) translatey(-84px) rotate(0deg);
  }

  to {
    -webkit-transform: rotate(360deg) translatey(-84px) rotate(-360deg);
            transform: rotate(360deg) translatey(-84px) rotate(-360deg);
  }
}
.mer {
  width: 3.5px;
  height: 3.5px;
  border-radius: 50%;
  background-color: #888;
  position: absolute;
  top: 1066.6666666667px;
  left: 50%;
  margin: -1.75px;
  -webkit-animation: rot-mer 0.88s infinite linear;
  animation: rot-mer 0.88s infinite linear;
  z-index: 200;
}

.mer-path {
  width: 168px;
  height: 168px;
  border-radius: 50%;
  z-index: 100;
  position: absolute;
  top: 1066.6666666667px;
  left: 50%;
  margin: -84px;
  border: solid 1px #444;
}

@-webkit-keyframes rot-ven {
  from {
    -webkit-transform: rotate(0deg) translatey(-90px) rotate(0deg);
  }
  to {
    -webkit-transform: rotate(360deg) translatey(-90px) rotate(-360deg);
  }
}
@-keyframes rot-ven {
  from {
    -webkit-transform: rotate(0deg) translatey(-90px) rotate(0deg);
            transform: rotate(0deg) translatey(-90px) rotate(0deg);
  }

  to {
    -webkit-transform: rotate(360deg) translatey(-90px) rotate(-360deg);
            transform: rotate(360deg) translatey(-90px) rotate(-360deg);
  }
}
.ven {
  width: 5.5px;
  height: 5.5px;
  border-radius: 50%;
  background-color: #f5f9be;
  position: absolute;
  top: 1066.6666666667px;
  left: 50%;
  margin: -2.75px;
  -webkit-animation: rot-ven 2.25s infinite linear;
  animation: rot-ven 2.25s infinite linear;
  z-index: 200;
}

.ven-path {
  width: 180px;
  height: 180px;
  border-radius: 50%;
  z-index: 100;
  position: absolute;
  top: 1066.6666666667px;
  left: 50%;
  margin: -90px;
  border: solid 1px #444;
}

@-webkit-keyframes rot-ear {
  from {
    -webkit-transform: rotate(0deg) translatey(-102px) rotate(0deg);
  }
  to {
    -webkit-transform: rotate(360deg) translatey(-102px) rotate(-360deg);
  }
}
@-keyframes rot-ear {
  from {
    -webkit-transform: rotate(0deg) translatey(-102px) rotate(0deg);
            transform: rotate(0deg) translatey(-102px) rotate(0deg);
  }

  to {
    -webkit-transform: rotate(360deg) translatey(-102px) rotate(-360deg);
            transform: rotate(360deg) translatey(-102px) rotate(-360deg);
  }
}
.ear {
  width: 7px;
  height: 7px;
  border-radius: 50%;
  background-color: #4b94f9;
  position: absolute;
  top: 1066.6666666667px;
  left: 50%;
  margin: -3.5px;
  -webkit-animation: rot-ear 3.65s infinite linear;
  animation: rot-ear 3.65s infinite linear;
  z-index: 200;
}

.ear-path {
  width: 204px;
  height: 204px;
  border-radius: 50%;
  z-index: 100;
  position: absolute;
  top: 1066.6666666667px;
  left: 50%;
  margin: -102px;
  border: solid 1px #444;
}

@-webkit-keyframes rot-mar {
  from {
    -webkit-transform: rotate(0deg) translatey(-118px) rotate(0deg);
  }
  to {
    -webkit-transform: rotate(360deg) translatey(-118px) rotate(-360deg);
  }
}
@-keyframes rot-mar {
  from {
    -webkit-transform: rotate(0deg) translatey(-118px) rotate(0deg);
            transform: rotate(0deg) translatey(-118px) rotate(0deg);
  }

  to {
    -webkit-transform: rotate(360deg) translatey(-118px) rotate(-360deg);
            transform: rotate(360deg) translatey(-118px) rotate(-360deg);
  }
}
.mar {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background-color: #dd411a;
  position: absolute;
  top: 1066.6666666667px;
  left: 50%;
  margin: -3px;
  -webkit-animation: rot-mar 6.87s infinite linear;
  animation: rot-mar 6.87s infinite linear;
  z-index: 200;
}

.mar-path {
  width: 236px;
  height: 236px;
  border-radius: 50%;
  z-index: 100;
  position: absolute;
  top: 1066.6666666667px;
  left: 50%;
  margin: -118px;
  border: solid 1px #444;
}

@-webkit-keyframes rot-jup {
  from {
    -webkit-transform: rotate(0deg) translatey(-228px) rotate(0deg);
  }
  to {
    -webkit-transform: rotate(360deg) translatey(-228px) rotate(-360deg);
  }
}
@-keyframes rot-jup {
  from {
    -webkit-transform: rotate(0deg) translatey(-228px) rotate(0deg);
            transform: rotate(0deg) translatey(-228px) rotate(0deg);
  }

  to {
    -webkit-transform: rotate(360deg) translatey(-228px) rotate(-360deg);
            transform: rotate(360deg) translatey(-228px) rotate(-360deg);
  }
}
.jup {
  width: 70px;
  height: 70px;
  border-radius: 50%;
  background-color: #eaad3b;
  position: absolute;
  top: 1066.6666666667px;
  left: 50%;
  margin: -35px;
  -webkit-animation: rot-jup 43.32s infinite linear;
  animation: rot-jup 43.32s infinite linear;
  z-index: 200;
}

.jup-path {
  width: 456px;
  height: 456px;
  border-radius: 50%;
  z-index: 100;
  position: absolute;
  top: 1066.6666666667px;
  left: 50%;
  margin: -228px;
  border: solid 1px #444;
}

@-webkit-keyframes rot-sat {
  from {
    -webkit-transform: rotate(0deg) translatey(-362px) rotate(0deg);
  }
  to {
    -webkit-transform: rotate(360deg) translatey(-362px) rotate(-360deg);
  }
}
@-keyframes rot-sat {
  from {
    -webkit-transform: rotate(0deg) translatey(-362px) rotate(0deg);
            transform: rotate(0deg) translatey(-362px) rotate(0deg);
  }

  to {
    -webkit-transform: rotate(360deg) translatey(-362px) rotate(-360deg);
            transform: rotate(360deg) translatey(-362px) rotate(-360deg);
  }
}
.sat {
  width: 58px;
  height: 58px;
  border-radius: 50%;
  background-color: #d6cd93;
  position: absolute;
  top: 1066.6666666667px;
  left: 50%;
  margin: -29px;
  -webkit-animation: rot-sat 107.59s infinite linear;
  animation: rot-sat 107.59s infinite linear;
  z-index: 200;
}

.sat-path {
  width: 724px;
  height: 724px;
  border-radius: 50%;
  z-index: 100;
  position: absolute;
  top: 1066.6666666667px;
  left: 50%;
  margin: -362px;
  border: solid 1px #444;
}

@-webkit-keyframes rot-ura {
  from {
    -webkit-transform: rotate(0deg) translatey(-648px) rotate(0deg);
  }
  to {
    -webkit-transform: rotate(360deg) translatey(-648px) rotate(-360deg);
  }
}
@-keyframes rot-ura {
  from {
    -webkit-transform: rotate(0deg) translatey(-648px) rotate(0deg);
            transform: rotate(0deg) translatey(-648px) rotate(0deg);
  }

  to {
    -webkit-transform: rotate(360deg) translatey(-648px) rotate(-360deg);
            transform: rotate(360deg) translatey(-648px) rotate(-360deg);
  }
}
.ura {
  width: 26px;
  height: 26px;
  border-radius: 50%;
  background-color: #bfeef2;
  position: absolute;
  top: 1066.6666666667px;
  left: 50%;
  margin: -13px;
  -webkit-animation: rot-ura 306.87s infinite linear;
  animation: rot-ura 306.87s infinite linear;
  z-index: 200;
}

.ura-path {
  width: 1296px;
  height: 1296px;
  border-radius: 50%;
  z-index: 100;
  position: absolute;
  top: 1066.6666666667px;
  left: 50%;
  margin: -648px;
  border: solid 1px #444;
}

@-webkit-keyframes rot-nep {
  from {
    -webkit-transform: rotate(0deg) translatey(-972px) rotate(0deg);
  }
  to {
    -webkit-transform: rotate(360deg) translatey(-972px) rotate(-360deg);
  }
}
@-keyframes rot-nep {
  from {
    -webkit-transform: rotate(0deg) translatey(-972px) rotate(0deg);
            transform: rotate(0deg) translatey(-972px) rotate(0deg);
  }

  to {
    -webkit-transform: rotate(360deg) translatey(-972px) rotate(-360deg);
            transform: rotate(360deg) translatey(-972px) rotate(-360deg);
  }
}
.nep {
  width: 24px;
  height: 24px;
  border-radius: 50%;
  background-color: #363ed7;
  position: absolute;
  top: 1066.6666666667px;
  left: 50%;
  margin: -12px;
  -webkit-animation: rot-nep 601.9s infinite linear;
  animation: rot-nep 601.9s infinite linear;
  z-index: 200;
}

.nep-path {
  width: 1944px;
  height: 1944px;
  border-radius: 50%;
  z-index: 100;
  position: absolute;
  top: 1066.6666666667px;
  left: 50%;
  margin: -972px;
  border: solid 1px #444;
}

@-webkit-keyframes rot-plu {
  from {
    -webkit-transform: rotate(0deg) translatey(-1246px) rotate(0deg);
  }
  to {
    -webkit-transform: rotate(360deg) translatey(-1246px) rotate(-360deg);
  }
}
@-keyframes rot-plu {
  from {
    -webkit-transform: rotate(0deg) translatey(-1246px) rotate(0deg);
            transform: rotate(0deg) translatey(-1246px) rotate(0deg);
  }

  to {
    -webkit-transform: rotate(360deg) translatey(-1246px) rotate(-360deg);
            transform: rotate(360deg) translatey(-1246px) rotate(-360deg);
  }
}
.plu {
  width: 3px;
  height: 3px;
  border-radius: 50%;
  background-color: #963;
  position: absolute;
  top: 1066.6666666667px;
  left: 50%;
  margin: -1.5px;
  -webkit-animation: rot-plu 904.65s infinite linear;
  animation: rot-plu 904.65s infinite linear;
  z-index: 200;
}

.plu-path {
  width: 2492px;
  height: 2492px;
  border-radius: 50%;
  z-index: 100;
  position: absolute;
  top: 1066.6666666667px;
  left: 50%;
  margin: -1246px;
  border: solid 1px #444;
}

@-webkit-keyframes rot-lune {
  from {
    -webkit-transform: rotate(0deg) translatey(-7px) rotate(0deg);
  }
  to {
    -webkit-transform: rotate(360deg) translatey(-7px) rotate(-360deg);
  }
}
@-keyframes rot-lune {
  from {
    -webkit-transform: rotate(0deg) translatey(-7px) rotate(0deg);
            transform: rotate(0deg) translatey(-7px) rotate(0deg);
  }

  to {
    -webkit-transform: rotate(360deg) translatey(-7px) rotate(-360deg);
            transform: rotate(360deg) translatey(-7px) rotate(-360deg);
  }
}
.lune {
  width: 2px;
  height: 2px;
  background-color: #fff;
  position: absolute;
  top: 50%;
  left: 50%;
  margin: -1.5px;
  -webkit-animation: rot-lune 0.27s infinite linear;
  animation: rot-lune 0.27s infinite linear;
}

.mar {
  background-image: repeating-linear-gradient(to bottom, #fff, #fff 1px, transparent 1px, transparent 5px);
}

@-webkit-keyframes rot-pho {
  from {
    -webkit-transform: rotate(0deg) translatey(-7px) rotate(0deg);
  }
  to {
    -webkit-transform: rotate(360deg) translatey(-7px) rotate(-360deg);
  }
}
@-keyframes rot-pho {
  from {
    -webkit-transform: rotate(0deg) translatey(-7px) rotate(0deg);
            transform: rotate(0deg) translatey(-7px) rotate(0deg);
  }

  to {
    -webkit-transform: rotate(360deg) translatey(-7px) rotate(-360deg);
            transform: rotate(360deg) translatey(-7px) rotate(-360deg);
  }
}
@-webkit-keyframes rot-dem {
  from {
    -webkit-transform: rotate(0deg) translatey(-9px) rotate(0deg);
  }
  to {
    -webkit-transform: rotate(360deg) translatey(-9px) rotate(-360deg);
  }
}
@-keyframes rot-dem {
  from {
    -webkit-transform: rotate(0deg) translatey(-9px) rotate(0deg);
            transform: rotate(0deg) translatey(-9px) rotate(0deg);
  }

  to {
    -webkit-transform: rotate(360deg) translatey(-9px) rotate(-360deg);
            transform: rotate(360deg) translatey(-9px) rotate(-360deg);
  }
}
.pho, .dem {
  width: 1px;
  height: 1px;
  background-color: #fff;
  position: absolute;
  top: 50%;
  left: 50%;
}

.pho {
  margin: -1px;
  -webkit-animation: rot-pho 0.15s infinite linear;
  animation: rot-pho 0.15s infinite linear;
}

.dem {
  margin: -1px;
  -webkit-animation: rot-dem 0.2s infinite linear;
  animation: rot-dem 0.2s infinite linear;
}

.jove {
  width: 2px;
  height: 2px;
  background-color: #fff;
  position: absolute;
  top: 35px;
  left: 50%;
}

@-webkit-keyframes rot-io {
  from {
    -webkit-transform: rotate(0deg) translatey(-39px) rotate(0deg);
  }
  to {
    -webkit-transform: rotate(360deg) translatey(-39px) rotate(-360deg);
  }
}
@-keyframes rot-io {
  from {
    -webkit-transform: rotate(0deg) translatey(-39px) rotate(0deg);
            transform: rotate(0deg) translatey(-39px) rotate(0deg);
  }

  to {
    -webkit-transform: rotate(360deg) translatey(-39px) rotate(-360deg);
            transform: rotate(360deg) translatey(-39px) rotate(-360deg);
  }
}
@-webkit-keyframes rot-eur {
  from {
    -webkit-transform: rotate(0deg) translatey(-41px) rotate(0deg);
  }
  to {
    -webkit-transform: rotate(360deg) translatey(-41px) rotate(-360deg);
  }
}
@-keyframes rot-eur {
  from {
    -webkit-transform: rotate(0deg) translatey(-41px) rotate(0deg);
            transform: rotate(0deg) translatey(-41px) rotate(0deg);
  }

  to {
    -webkit-transform: rotate(360deg) translatey(-41px) rotate(-360deg);
            transform: rotate(360deg) translatey(-41px) rotate(-360deg);
  }
}
@-webkit-keyframes rot-gan {
  from {
    -webkit-transform: rotate(0deg) translatey(-45px) rotate(0deg);
  }
  to {
    -webkit-transform: rotate(360deg) translatey(-45px) rotate(-360deg);
  }
}
@-keyframes rot-gan {
  from {
    -webkit-transform: rotate(0deg) translatey(-45px) rotate(0deg);
            transform: rotate(0deg) translatey(-45px) rotate(0deg);
  }

  to {
    -webkit-transform: rotate(360deg) translatey(-45px) rotate(-360deg);
            transform: rotate(360deg) translatey(-45px) rotate(-360deg);
  }
}
@-webkit-keyframes rot-cal {
  from {
    -webkit-transform: rotate(0deg) translatey(-53px) rotate(0deg);
  }
  to {
    -webkit-transform: rotate(360deg) translatey(-53px) rotate(-360deg);
  }
}
@-keyframes rot-cal {
  from {
    -webkit-transform: rotate(0deg) translatey(-53px) rotate(0deg);
            transform: rotate(0deg) translatey(-53px) rotate(0deg);
  }

  to {
    -webkit-transform: rotate(360deg) translatey(-53px) rotate(-360deg);
            transform: rotate(360deg) translatey(-53px) rotate(-360deg);
  }
}
.io {
  -webkit-animation: rot-io 0.2s infinite linear;
  animation: rot-io 0.2s infinite linear;
}

.eur {
  -webkit-animation: rot-eur 0.35s infinite linear;
  animation: rot-eur 0.35s infinite linear;
}

.gan {
  -webkit-animation: rot-gan 0.7s infinite linear;
  animation: rot-gan 0.7s infinite linear;
}

.cal {
  -webkit-animation: rot-cal 1.65s infinite linear;
  animation: rot-cal 1.65s infinite linear;
}

.jup {
  background-image: repeating-linear-gradient(6deg, #797663 22px, #e1dcde 16px, #c3a992 30px, #e9ece2 30px);
}

.spot {
  position: absolute;
  width: 16px;
  height: 12px;
  border-radius: 8px / 6px;
  top: 45px;
  left: 50%;
  background-color: #bc833b;
  -webkit-box-shadow: 0px 0px 5px #e1dcde;
          box-shadow: 0px 0px 5px #e1dcde;
  border: solid 1px #e1dcde;
  box-sizing: border-box;
  -moz-box-sizing: border-box;
  -webkit-box-sizing: border-box;
  z-index: 300;
}
.nep .spot {
  background-color: #29319d;
  border: 0;
  -webkit-box-shadow: none;
          box-shadow: none;
  top: 50%;
  left: 45%;
  width: 10px;
  height: 6px;
  margin: -2px;
  border-radius: 5px / 3px;
  border-left: solid 1px #7ed6fe;
}

div[class$="-ring"] {
  border-radius: 50%;
  position: absolute;
  top: 50%;
  left: 50%;
  opacity: 0.7;
  -webkit-transform: rotatex(45deg);
  transform: rotatex(45deg);
}

.a-ring {
  border: solid 5px #96866f;
  width: 119px;
  height: 119px;
  margin: -64.5px;
}

.b-ring {
  border: solid 10px #554c3c;
  width: 104px;
  height: 104px;
  margin: -62px;
}

.c-ring {
  border: solid 9px #574f4a;
  width: 95px;
  height: 95px;
  margin: -56.5px;
}

.f-ring {
  border: solid 2px #908e8d;
  width: 133px;
  height: 133px;
  margin: -68.5px;
}

.e-ring {
  border: solid 7px #908e8d;
  width: 76px;
  height: 76px;
  margin: -45px;
  -webkit-transform: rotatex(0deg) rotatey(89deg) !important;
  transform: rotatex(0deg) rotatey(89deg) !important;
}

.plu, .plu-path {
  top: 1354.0666666667px;
}

</style>