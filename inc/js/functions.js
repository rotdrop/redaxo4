var IE6 = false; // via conditional comment auf true
var THIS; 		 // globaler Selector
var Camerata = new Class({
	initialize: function() {
		this.eHeader    = $('header');
		this.eContent   = $('content');
		this.eFooter    = $('footer');	
		this.isHome     = $(document.body).hasClass('home');
		this.setContentMinHeight();
	},
	getElementDimensions:function(){
		this.vClientH   = $(document.body).getHeight().toInt();
		this.vHeaderH   = this.eHeader.getHeight().toInt();
		this.vContentH  = this.eContent.getHeight().toInt();
		this.vContentPB = this.eContent.getStyle('padding-bottom').toInt();
		this.vFooterH   = (this.eFooter) ? this.eFooter.getHeight().toInt() : 0 ;
	},
	setContentMinHeight:function(){
		if(this.isHome) return; // not on frontpage 
		this.getElementDimensions();
		var newMinHeight = this.vClientH - (this.vHeaderH + this.vFooterH + this.vContentPB);
		//alert(this.vClientH);
		this.eContent.setStyle('min-height',newMinHeight+'px');
		if (IE6) this.eContent.setStyle('height',newMinHeight+'px');
	},
	unCryptMailto:function(s) {
		var n=0;
		var r="";
		for (var i=0;i<s.length;i++) {
			n=s.charCodeAt(i);
			if (n>=8364) {n = 128;}
			r += String.fromCharCode(n-3);
		}
		return r;
	},
	mailTo:function(s) {
		location.href=this.unCryptMailto(s);
	}
});
unCryptMailto = function(e) {
	THIS.unCryptMailto(e);	
}
window.addEvents({
	'domready':function(){
		THIS = new Camerata();
		if (IE6) {
			//DD_belatedPNG.fix('#container'); // argument is a CSS selector
		}
	}
});
window.onresize = function(){
	THIS.setContentMinHeight();
}