<script src="/admin/js/jquery-1.7.2.min.js" language="javascript" type="text/javascript"></script>

<script type="text/javascript">
$(document).ready(function () {

var lowest_note = 40;
var string_count = 6;
var fret_count = 24;


function string2note(string, fret)
{
    var fret_offset = 0;
    if (string <= 2) fret_offset = 1;

    return lowest_note + fret + (string_count - string) * 5 - fret_offset;
}


    $('#go').click(function () {

        var max_diff_from_lowest = 28; // 2 octaves + 3 notes + 1 note (-1 pos)
        var max_diff_from_highest = 28; // 2 octaves + 3 notes + 1 note (4th pos)

        var string = parseInt($('#string').val());
        var new_string = string;
        var fret = parseInt($('#fret').val());
        var new_fret = fret;
        var pos = parseInt($('#pos').val());
        var force_pos = parseInt($('#force_pos').val());
        var next_note = parseInt($('#next').val());
        var max_diff_manual = parseInt($('#max_diff').val());

        var playModeLegato = $('#legato').attr('checked') == 'checked' ? 1 : 0;
        var playModeSlide = $('#slide').attr('checked') == 'checked' ? 1 : 0;

        if (pos == 0)
        {
            max_diff_from_lowest--;
        }

        var dir = 1;


        var cur_note = string2note(string, fret); // last note number
        if (cur_note > next_note) dir = -1;
        var cur_finger_pos = fret - pos; // last note position in finger position (eg. hand position (POS) = 5, then 8th fret is 3rd finger position)
        var diff = Math.abs(next_note - cur_note); // difference between last and next notes
        var max_diff;
        var possible_diff = diff;
        var next_finger_pos;
        var finger; // number of finger which presses the string
        var next_finger; // number of finger for next note
        var first_pos_note;
        // fingers: 1 is index finger, 2 is middle finger, 3 is ring finger, 4 is little finger
        finger = cur_finger_pos+1; // the scheme is simple: fret[0] = 1(finger), fret[1] = 2, fret[2] = 3, fret[3] = 4, fret[4] = 4
        if (finger > 4) finger = 4;

        if (dir > 0)
        {
            var f4 = 0; // can we take 4 frets as interval?

            next_finger_pos = possible_diff + cur_finger_pos; // estimated finger position of next note
            // if as = 1 (or f4 = 0), this means changing string if next note is on 4th finger position.
            // So, if you don't want to jump on next string (eg. tapping or slides) - add condition here
            // For now, it keeps same string only if legato mode is on
            // Also, we can't jump on next string if next finger position going to be -1 (if we are on 0th pos, except 3rd string)
            if ((playModeLegato == 1 && Math.abs(next_finger_pos - cur_finger_pos) <= 4) || (pos == 0 && string != 3)) {
                f4 = 1;
            }

            first_pos_note = string2note(string_count, pos-1);

            max_diff = max_diff_from_lowest - Math.abs(cur_note - first_pos_note) + f4; // if legato, then 4 frets possible, if not (0) then 3 possible in position
            if (max_diff_manual < max_diff) {
                max_diff = max_diff_manual;
            }

            if (diff > max_diff)
            {
                possible_diff = max_diff;
            }

            next_finger_pos = possible_diff + cur_finger_pos; // estimated finger position of next note

            var as = 1 - f4;

            var af = as; // fret offset factor equals string offset factor, except if transition contains 3rd string

            // when calculating string, offset (as) depends on 3rd string: does the interval between last and next notes contains third-to-second string transition?
            // if yes, as offset is incremented
            if ( string >= 3 && (next_finger_pos - 5 * (string-3) > 3 + f4))
            {
                as++;
            }

            new_string = string - Math.floor((next_finger_pos+as) / 5);
            new_fret = pos + (next_finger_pos+as) % 5 - af;
        } else {
            var fm1 = 1; // can we descend by 1 fret left from position?

            if (pos == 0 || cur_finger_pos == 4)
            {
                fm1 = 0;
            }

            first_pos_note = string2note(1, pos+4);

            max_diff = max_diff_from_highest - Math.abs(cur_note - first_pos_note) + fm1;
            if (max_diff_manual < max_diff) {
                max_diff = max_diff_manual;
            }

            if (diff > max_diff)
            {
                possible_diff = max_diff;
            }

            next_finger_pos = cur_finger_pos - possible_diff; // estimated finger position of next note

            var as = 1 - fm1;

            var af = as - (cur_finger_pos-3); // fret offset factor equals string offset factor, except if transition contains 3rd string

            // when calculating string, offset (as) depends on 3rd string: does the interval between last and next notes contains third-to-second string transition?
            // if yes, as offset is incremented
            if (string <  3 && (next_finger_pos + 5 * (2-string) < 0-fm1 ))
            {
                as++;
            }

//            new_string = string - Math.floor((4 - next_finger_pos - as) / 5);
            new_string = string + Math.floor( Math.abs( next_finger_pos - 3 - as) / 5 );
            new_fret = pos + cur_finger_pos + (next_finger_pos - 3 - as) % 5 + af;
        }

        if (diff > max_diff)
        {
            // move pos
            pos = new_fret + dir * (diff - max_diff);
            new_fret = pos;
        }

        if (playModeSlide == 1 && ((dir * diff - cur_finger_pos > 4) || (dir * diff + cur_finger_pos < -1)) && (pos + dir * diff <= fret_count) && (pos + dir * diff >= 1))
        {
            pos = pos + dir * diff;
            new_string = string;
            new_fret = pos;
        }

        if (force_pos >= 0 && force_pos <= fret_count-3)
        {
            if (pos >= force_pos-1 && pos <= force_pos+3)
            {
                pos = force_pos;
            }
        }

        if (pos > fret_count - 3) pos = fret_count - 3;
        if (pos < 0) pos = 0;

        next_finger = new_fret - pos + 1;
        if (next_finger > 4) next_finger = 4;
        if (next_finger <= 0 && pos > 0) next_finger = 1;
        if (pos == 0) next_finger = next_finger - 1;
        $('#result').html("String: " + new_string + "; Fret: " + new_fret + "; Pos: " + pos + "; Finger: " + next_finger + ";");

    });

    $('#number').click(function () {
        $('#note_number').html(string2note(parseInt($('#string').val()), parseInt($('#fret').val())))
    });
});
</script>


    Pos: <input type="text" value="" id="pos" /><br />
    String: <input type="text" value="" id="string" /><br />
    Fret: <input type="text" value="" id="fret" /><br />
    Next: <input type="text" value="" id="next" /><br />
    Max Diff: <input type="text" value="" id="max_diff" /><br />
    Force Pos: <input type="text" value="" id="force_pos" /><br />
    Legato: <input type="checkbox" value="1" id="legato" /><br />
    Slide: <input type="checkbox" value="1" id="slide" /><br />
    <input type="button" value="Go" id="go" /><input type="button" value="Number" id="number" />
<div id="result"></div>
<div id="note_number"></div>
