<?php
define('MEMORY_SIZE', 1024);

class CPU {
    public $registers = [0, 0, 0, 0];
    public $memory = [];
    public $flags = 0;

    public function mov($src, $dest) {
        if ($dest < 4) {
            $this->registers[$dest] = $src;
        } else {
            $this->memory[$dest - 4] = $src;
        }
    }

    public function add($a, $b) {
        if ($b < 4) {
            $this->registers[$b] += $a;
        } else {
            $this->memory[$b - 4] += $a;
        }
    }

    public function sub($a, $b) {
        if ($b < 4) {
            $this->registers[$b] -= $a;
        } else {
            $this->memory[$b - 4] -= $a;
        }
    }

    public function jmp($address) {
        $this->flags = 0;
        $this->registers[3] = $address;
    }

    public function cmp($a, $b) {
        $result = $b - $a;
        $this->flags = 0;
        if ($result == 0) {
            $this->flags |= 1; // zero flag
        }
        if ($result < 0) {
            $this->flags |= 2; // negative flag
        }
        if ($b < $a) {
            $this->flags |= 4; // carry flag
        }
    }

    public function je($address) {
        if ($this->flags & 1) {
            $this->jmp($address);
        }
    }
}

$cpu = new CPU();
$cpu->registers[0] = 0; // eax
$cpu->registers[3] = 50; // esi
$cpu->memory[0] = 0xeb; // jmp loop
$cpu->memory[1] = 0xf9; // offset from current address (-7)

while ($cpu->registers[3] >= 0 && $cpu->registers[3] < MEMORY_SIZE - 1) {
    $instruction = $cpu->memory[$cpu->registers[3]];
    $operand = unpack('c', $cpu->memory[$cpu->registers[3] + 1])[1];
    $cpu->registers[3] += 2;
    switch ($instruction) {
        case 0x89: // mov reg, reg/mem
            $cpu->mov($cpu->registers[($operand >> 3) & 7], $operand & 7);
            break;
        case 0x01: // add reg/mem, reg
            $cpu->add($cpu->registers[$operand & 7], ($operand >> 3) & 7);
            break;
        case 0x29: // sub reg/mem, reg
            $cpu->sub($cpu->registers[$operand & 7], ($operand >> 3) & 7);
            break;
        case 0xeb: // jmp rel8
            $cpu->jmp($cpu->registers[3] + $operand);
            break;
        case 0x3b: // cmp reg/mem, reg
            $cpu->cmp($cpu->registers[$operand & 7], ($operand >> 3)

                & 7);
            break;
        case 0x74: // je rel8
            $cpu->je($cpu->registers[3] + $operand);
            break;
        default:
            printf("Invalid instruction: %x\n", $instruction);
            exit(1);
    }
}

printf("eax: %d\n", $cpu->registers[0]);
