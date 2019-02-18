var codonTable = new Object();
codonTable['aaa'] = 'k';
codonTable['aat'] = 'n';
codonTable['aag'] = 'k';
codonTable['aac'] = 'n';
codonTable['ata'] = 'i';
codonTable['att'] = 'i';
codonTable['atg'] = 'm';
codonTable['atc'] = 'i';
codonTable['aga'] = 'r';
codonTable['agt'] = 's';
codonTable['agg'] = 'r';
codonTable['agc'] = 's';
codonTable['aca'] = 't';
codonTable['act'] = 't';
codonTable['acg'] = 't';
codonTable['acc'] = 't';
codonTable['taa'] = '*';
codonTable['tat'] = 'y';
codonTable['tag'] = '*';
codonTable['tac'] = 'y';
codonTable['tta'] = 'l';
codonTable['ttt'] = 'f';
codonTable['ttg'] = 'l';
codonTable['ttc'] = 'f';
codonTable['tga'] = '*';
codonTable['tgt'] = 'c';
codonTable['tgg'] = 'w';
codonTable['tgc'] = 'c';
codonTable['tca'] = 's';
codonTable['tct'] = 's';
codonTable['tcg'] = 's';
codonTable['tcc'] = 's';
codonTable['gaa'] = 'e';
codonTable['gat'] = 'd';
codonTable['gag'] = 'e';
codonTable['gac'] = 'd';
codonTable['gta'] = 'v';
codonTable['gtt'] = 'v';
codonTable['gtg'] = 'v';
codonTable['gtc'] = 'v';
codonTable['gga'] = 'g';
codonTable['ggt'] = 'g';
codonTable['ggg'] = 'g';
codonTable['ggc'] = 'g';
codonTable['gca'] = 'a';
codonTable['gct'] = 'a';
codonTable['gcg'] = 'a';
codonTable['gcc'] = 'a';
codonTable['caa'] = 'q';
codonTable['cat'] = 'h';
codonTable['cag'] = 'q';
codonTable['cac'] = 'h';
codonTable['cta'] = 'l';
codonTable['ctt'] = 'l';
codonTable['ctg'] = 'l';
codonTable['ctc'] = 'l';
codonTable['cga'] = 'r';
codonTable['cgt'] = 'r';
codonTable['cgg'] = 'r';
codonTable['cgc'] = 'r';
codonTable['cca'] = 'p';
codonTable['cct'] = 'p';
codonTable['ccg'] = 'p';
codonTable['ccc'] = 'p';

function convertToAminoAcid(sequence, frame = 0) {
	//Shift based on length
    sequence = sequence.substr(frame);
    sequence = sequence.toLowerCase();
    var aaSeq = "";
    
    //Loop and convert, use multiple of 3 to avoid problems
    for (var i = 0; i < Math.floor(sequence.length/3) * 3; i += 3) {
    	codon = sequence.substr(i,3);
        //Make sure viable codon first, if not X unknown
        if (!(codon in codonTable)) {
        	aaSeq += "x";
        }
        else {
    		aaSeq += codonTable[codon];
        }
    }

    return aaSeq;
}
