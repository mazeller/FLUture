<?php
require 'autoload.php';
$theme = new Sample\Theme('');
$scripts = $theme->getOption('head_script');
$scripts["file"] = array("/js/jquery.min.js","/js/jquery-ui.min.js","/js/c3.min.js","/js/d3.v3.min.js","/js/dataloader.js","/js/jQDateRangeSlider-withRuler-min.js");
$theme->setOption('head_script',$scripts,true);
$theme->addStyle('{{asset_path}}/css/c3.min.css');
$theme->addStyle('{{asset_path}}/css/jquery-ui.css');
$theme->addStyle('{{asset_path}}/css/iThing-min.css');

$theme->drawHeader();
?>

<table class="wd-Table--striped">
    <colgroup>
       <col span="1">
       <col span="1">
       <col span="1">
       <col span="1" style="width: 50%;">
       <col span="1">
    </colgroup>
    <tbody>
        <tr>
            <td><strong>Subtype</strong></td>
            <td><strong>Designation</strong></td>
            <td><strong>Global Nomenclature</strong></td>
            <td><strong>Description</strong></td>
            <td><strong>Distribution</strong></td>
        </tr>
        <tr>
            <td>H1</td>
            <td>alpha</td>
            <td>1A.1.1</td>
            <td>Alpha (&alpha;) viruses belong to the 1A classical lineage viruses related to the 1918 human influenza pandemic that circulated from 1930s to current.</td>
            <td>Canada, Hong Kong, South Korea, Taiwan, USA</td>
        </tr>
        <tr>
            <td>H1</td>
            <td>beta</td>
            <td>1A.2</td>
            <td>
                Beta (&beta;) clade belong to the 1A classical lineage and developed due to reassortment events between classical H1N1 isolates and H3N2 viruses in pigs that led to H1N1 viruses expressing cH1N1 HA and NA surface proteins
                with H3N2 internal genes.
            </td>
            <td>Mexico, South Korea, USA</td>
        </tr>
        <tr>
            <td>H1</td>
            <td>gamma</td>
            <td>1A.3.3.3</td>
            <td>Gamma (&gamma;) clade&nbsp; belong to the 1A classical lineage and such viruses are referred to as H1N2-like isolates and are the result of a triple reassortment event between H3N2 and cH1N1 viruses.</td>
            <td>South Korea, USA</td>
        </tr>
        <tr>
            <td>H1</td>
            <td>gamma2</td>
            <td>1A.3.2</td>
            <td>
                Gamma 2 ( &gamma; -2) is a rarely detected clade belonging to the 1A classical lineage that shared a common ancestor with H1&gamma; and H1pdm09 and circulated in swine since approximately 1995 but remained undetected until
                2003.
            </td>
            <td>Mexico, USA</td>
        </tr>
        <tr>
            <td>H1</td>
            <td>gamma-like</td>
            <td>1A.3.3-like</td>
            <td>
                H1 HA sequence does not belong to a gamma clade but share an internal node or is phylogenetically closest to H1 gamma. 
            </td>
            <td>USA</td>
        </tr>
        <tr>
            <td>H1</td>
            <td>gamma-npdm-like*</td>
            <td>gamma-npdm-like*</td>
            <td>
                H1 HA sequence does not belong to either gamma or the new pandemic clade but share an internal node with these two clades.
            </td>
            <td>USA</td>
        </tr>
        <tr>
            <td>H1</td>
            <td>gamma2-beta-like*</td>
            <td>gamma2-beta-like*</td>
            <td>
                Historic classical swine H1N1 virus that was no longer detected in US swine until recently in 2018 after the LAIV vaccine for use in swine was released commercially in the US.
            </td>
            <td>USA</td>
        </tr>
        <tr>
            <td>H1</td>
            <td>delta1</td>
            <td>1B.2.2</td>
            <td>
                Swine influenza viruses belonging to the Delta 1 clade are highly divergent, containing both H1N1 and H1N2 swine influenza viruses. &nbsp;Delta 1 and Delta 2 likely emerged as the result of two separate but nearly
                contemporaneous introductions of human IAV into swine first detected in the early 2000&rsquo;s.
            </td>
            <td>Argentina, Brazil, Canada, United Kingdom, USA</td>
        </tr>
        <tr>
            <td>H1</td>
            <td>delta1a</td>
            <td>1B.2.2.1</td>
            <td>
                The &delta;1-viruses diversified into two new genetic clades, H1-&delta;1a (1B.2.2.1) and H1-&delta;1b (1B.2.2.2), which were also antigenically distinct from the earlier H1-&delta;1-viruses. Differentiation between
                H1-&delta;1a (1B.2.2.1) and H1-&delta;1b (1B.2.2.2) viruses was associated with four amino acid differences (E74K, S85P, D86E, G186E).
            </td>
            <td>USA</td>
        </tr>
        <tr>
            <td>H1</td>
            <td>delta1b</td>
            <td>1B.2.2.2</td>
            <td>
                The &delta;1-viruses diversified into two new genetic clades, H1-&delta;1a (1B.2.2.1) and H1-&delta;1b (1B.2.2.2), which were also antigenically distinct from the earlier H1-&delta;1-viruses.Differentiation between
                H1-&delta;1a (1B.2.2.1) and H1-&delta;1b (1B.2.2.2) viruses was associated with four amino acid differences (E74K, S85P, D86E, G186E).
            </td>
            <td>USA</td>
        </tr>
        <tr>
            <td>H1</td>
            <td>delta2</td>
            <td>1B.2.1</td>
            <td>
                In 2005 H1N1 viruses with human-origin H1 and N1 segments were identified in the United States. Delta 1 and Delta 2 likely emerged as the result of two separate but nearly contemporaneous introductions into swine first
                detected in the early 2000&rsquo;s.
            </td>
            <td>USA</td>
        </tr>
        <tr>
            <td>H1</td>
            <td>delta-like</td>
            <td>delta-like</td>
            <td>
                H1 HA sequence does not belong to a delta clade but share an internal node or is phylogenetically closest to H1 delta clade.
            </td>
            <td>USA</td>
        </tr>
        <tr>
            <td>H1</td>
            <td>pdmH1</td>
            <td>1A.3.3.2</td>
            <td>
                In 2009, the first pandemic of the 21st century occurred with the introduction of a swine-origin influenza virus of the cH1N1 subtype into the human population that transmitted easily between people. The pdmH1 spilled back
                from humans into swine and became an established clade.
            </td>
            <td>37 countries</td>
        </tr>
        <tr>
            <td>H1</td>
            <td>Eurasian_avian-like</td>
            <td>Eurasian_avian-like</td>
            <td>Eurasian avian lineage emerged from an introduction from wild birds into pigs in the 1970s.</td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td>H3</td>
            <td>cluster_I</td>
            <td>3.1990.1</td>
            <td>
                In 1998&ndash;1999, a triple-reassortant H3N2 influenza virus was identified in U.S. swine that possessed H3, N2, and PB1 segments of seasonal human H3N2 virus origin, PB2 and PA segments of avian virus origin, and NP, M,
                and NS segments of classical H1N1 swine virus origin. Cluster_I is a historic swine H3N2 virus that was derived from one of the three distinct human seasonal H3N2 introductions from 1995, 1997 and 1996.
            </td>
            <td>North America, South Korea, China</td>
        </tr>
        <tr>
            <td>H3</td>
            <td>cluster_IV</td>
            <td>3.1990.4</td>
            <td>Swine Clade IV (C‐IV) H3N2 IAV contains an H3 from a late 1990s human‐to‐swine introduction and continues to circulate in North American swine since approximately 2005.</td>
            <td>North America, South Korea, China</td>
        </tr>
        <tr>
            <td>H3</td>
            <td>cluster_IVA</td>
            <td>3.1990.4.a</td>
            <td>Swine Clade IV diversified into a genetically and antigenically different clade termed cluster_IVA.</td>
            <td>North America, South Korea, China</td>
        </tr>
        <tr>
            <td>H3</td>
            <td>cluster_IVB</td>
            <td>3.1990.4.b</td>
            <td>Swine Clade IV diversified into a genetically and antigenically different clade termed cluster_IVB.</td>
            <td>North America, South Korea, China</td>
        </tr>
        <tr>
            <td>H3</td>
            <td>cluster_IVC</td>
            <td>3.1990.4.c</td>
            <td>Swine Clade IV diversified into a genetically and antigenically different clade termed cluster_IVC.</td>
            <td>North America, South Korea, China</td>
        </tr>
        <tr>
            <td>H3</td>
            <td>cluster_IVD</td>
            <td>3.1990.4.d</td>
            <td>Swine Clade IV diversified into a genetically and antigenically different clade termed cluster_IVD.</td>
            <td>North America, South Korea, China</td>
        </tr>
        <tr>
            <td>H3</td>
            <td>cluster_IVE</td>
            <td>3.1990.4.e</td>
            <td>Swine Clade IV diversified into a genetically and antigenically different clade termed cluster_IVE.</td>
            <td>North America, South Korea, China</td>
        </tr>
        <tr>
            <td>H3</td>
            <td>cluster_IVF</td>
            <td>3.1990.4.f</td>
            <td>Swine Clade IV diversified into a genetically and antigenically different clade termed cluster_IVF.</td>
            <td>North America, South Korea, China</td>
        </tr>
        <tr>
            <td>H3</td>
            <td>2010.1</td>
            <td>3.2010.1</td>
            <td>H3 2010.1 was the first successful transmission of virus from humans to swine in the 2010 decade (TMRCA-2011) that is now established in US swine.</td>
            <td>North America</td>
        </tr>
        <tr>
            <td>H3</td>
            <td>2010.2</td>
            <td>3.2010.2</td>
            <td>H3 2010.2 was the second successful transmission of virus from humans to swine in the 2010 decade (TMRCA-2016) that is now established in US swine.</td>
            <td>USA</td>
        </tr>
        <tr>
            <td>H3</td>
            <td>HA-human-to-swine-2013</td>
            <td>human-to-swine-2013</td>
            <td>One off event of human to swine transmission that was detected in 2013 with less than 5 cases</td>
            <td>USA</td>
        </tr>
        <tr>
            <td>H3</td>
            <td>HA-human-to-swine-2016</td>
            <td>human-to-swine-2016</td>
            <td>One off event of human to swine transmission that was detected in 2016 with less than 5 cases</td>
            <td>USA</td>
        </tr>
        <tr>
            <td>H3</td>
            <td>HA-human-to-swine-2017</td>
            <td>human-to-swine-2017</td>
            <td>One off event of human to swine transmission that was detected in 2017 with less than 5 cases</td>
            <td>USA</td>
        </tr>
        <tr>
            <td>H3</td>
            <td>HA-human-to-swine-2018</td>
            <td>human-to-swine-2018</td>
            <td>One off event of human to swine transmission that was detected in 2018 with less than 5 cases</td>
            <td>USA</td>
        </tr>
    </tbody>
</table>
<p><b>*</b> the "-like" designation indicates that a query sequence shares a common ancestor with the 2 named clades, but does not fall within the named clade. </p>

<?php
$theme->drawFooter();
