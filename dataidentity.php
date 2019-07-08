<!DOCTYPE html>
<html>
<body>
<script src="/js/jquery.min.js"></script>
<script src="/js/jquery-ui.min.js"></script>
<script src="/js/c3.min.js"></script>
<script src="/js/d3.v3.min.js"></script>
<script src="/js/cleandata.js"></script>

<script>
$(document).ready(function() {
        getFluRecords(getBlastResult);
});

function updateDB(data) {
        updateDBRecords(data);
        return;
}

function getCladeValue(seqdata, callerType, callback) {
        for(i = 0; i < 20; i++) {
            //Process fasta input into specific object structure
            var fastaString = seqdata[i].sequence;
            var case_name = seqdata[i].case_name;

            //Request data
            $.ajax({
                    url: '/identifyseqdata.php',
                    type: 'post',
                    data: {'seq': fastaString, 'caller': callerType, 'case': case_name},
                    success: function(data, status) {
                            var data = data;
                            callback(data);
                    },
                    error: function(xhr, desc, err) {
                            console.log(xhr);
                            console.log("Details: " + desc + "\nError:" + err);
                    }
            });
        }
}

function getBlastResult(data) {
        if(data.hasOwnProperty("haseq")) {
            getCladeValue(data.haseq, "HA", updateDB);
        }

        if(data.hasOwnProperty("naseq")) {
            getCladeValue(data.naseq, "NA", updateDB);
        }

        return;
}
</script>
</body>
</html>
